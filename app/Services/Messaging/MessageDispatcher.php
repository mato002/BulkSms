<?php

namespace App\Services\Messaging;

use App\Models\Channel;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Message;
use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\UrlShortenerService;
use App\Services\CredentialEncryptionService;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageDispatcher
{
    public function __construct(private readonly Encrypter $encrypter)
    {
    }

    public function dispatch(OutboundMessage $outbound): Message
    {
        // Try to find existing channel
        $channelConfig = Channel::query()
            ->where('client_id', $outbound->clientId)
            ->where('name', $outbound->channel)
            ->where('active', true)
            ->first();

        // If channel doesn't exist, create it with system defaults
        if (!$channelConfig) {
            $channelConfig = $this->createDefaultChannel($outbound->clientId, $outbound->channel);
        }

        // Handle credentials - decrypt if encrypted, otherwise parse JSON
        $credentials = $channelConfig->credentials;
        if (is_string($credentials)) {
            $credentialService = app(CredentialEncryptionService::class);
            $credentials = $credentialService->getCredentials($credentials);
        } else {
            $credentials = (array) $credentials;
        }
        $provider = $channelConfig->provider;

        /** @var MessageSender $sender */
        $sender = App::makeWith(MessageSender::class.'@'.$provider, [
            'credentials' => $credentials,
        ]);

        return DB::transaction(function () use ($outbound, $provider, $sender) {
            $message = new Message();
            $message->client_id = $outbound->clientId;
            $message->template_id = $outbound->templateId;
            $message->channel = $outbound->channel;
            $message->direction = 'outbound'; // Mark as outbound
            $message->provider = $sender->provider();
            $message->sender = $outbound->sender;
            $message->recipient = $outbound->recipient;
            $message->subject = $outbound->subject;
            $message->body = $outbound->body;
            $message->status = 'sending';
            $message->metadata = $outbound->metadata;
            $message->is_read = true; // Outbound messages are already "read" by sender
            $message->save();

            // Generate reply link and append to message body for SMS
            $messageBody = $outbound->body;
            if ($outbound->channel === 'sms') {
                // Use URL shortener service to create a short link
                $urlShortener = app(UrlShortenerService::class);
                $shortUrl = $urlShortener->createShortLink($message->id);
                
                // Append short URL to message
                $messageBody .= "\n\nReply: {$shortUrl}";
                
                // Update the message body with the reply link
                $message->body = $messageBody;
                $message->save();
                
                // Create new OutboundMessage with updated body
                $outboundWithLink = new OutboundMessage(
                    clientId: $outbound->clientId,
                    channel: $outbound->channel,
                    recipient: $outbound->recipient,
                    sender: $outbound->sender,
                    body: $messageBody,
                    templateId: $outbound->templateId,
                    subject: $outbound->subject,
                    metadata: $outbound->metadata
                );
            } else {
                $outboundWithLink = $outbound;
            }

            try {
                $providerMessageId = $sender->send($outboundWithLink);
                $message->status = 'sent';
                $message->provider_message_id = $providerMessageId;
                $message->sent_at = now();
            } catch (\Throwable $e) {
                $message->status = 'failed';
                $message->failed_at = now();
                $message->error_message = $e->getMessage();
                
                // Create notification for message failure (batch failures to avoid spam)
                // Only notify if this is a significant failure or part of a batch
                try {
                    // Check if there are multiple recent failures (within last 5 minutes)
                    $recentFailures = \App\Models\Message::where('client_id', $outbound->clientId)
                        ->where('status', 'failed')
                        ->where('failed_at', '>=', now()->subMinutes(5))
                        ->count();
                    
                    // Only notify if this is the 5th, 10th, 20th, etc. failure (to batch notifications)
                    if ($recentFailures > 0 && ($recentFailures % 5 == 0)) {
                        \App\Models\Notification::messagesFailed(
                            $outbound->clientId,
                            $recentFailures,
                            'Multiple messages failed to send'
                        );
                    }
                } catch (\Exception $notificationError) {
                    Log::warning('Failed to create message failure notification', ['error' => $notificationError->getMessage()]);
                }
            }

            $message->save();

            // Create or update conversation
            $this->ensureConversation($message);

            return $message;
        });
    }

    /**
     * Ensure a conversation exists for this message and update it
     */
    private function ensureConversation(Message $message): void
    {
        // Find or create contact
        $contact = Contact::firstOrCreate(
            [
                'client_id' => $message->client_id,
                'contact' => $message->recipient,
            ],
            [
                'name' => $message->recipient, // Use phone as name initially
                'department' => null,
            ]
        );

        // Find or create conversation
        $conversation = DB::table('conversations')
            ->where('client_id', $message->client_id)
            ->where('contact_id', $contact->id)
            ->where('channel', $message->channel)
            ->first();

        if (!$conversation) {
            // Create new conversation
            $conversationId = DB::table('conversations')->insertGetId([
                'client_id' => $message->client_id,
                'contact_id' => $contact->id,
                'contact_identifier' => $message->recipient,
                'channel' => $message->channel,
                'status' => 'open',
                'unread_count' => 0,
                'last_message_preview' => substr($message->body, 0, 100),
                'last_message_direction' => $message->direction,
                'last_message_at' => $message->created_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Update existing conversation
            $conversationId = $conversation->id;
            DB::table('conversations')
                ->where('id', $conversationId)
                ->update([
                    'last_message_preview' => substr($message->body, 0, 100),
                    'last_message_direction' => $message->direction,
                    'last_message_at' => $message->created_at,
                    'updated_at' => now(),
                ]);
        }

        // Link message to conversation
        $message->conversation_id = $conversationId;
        $message->save();

        // Update contact stats
        DB::table('contacts')
            ->where('id', $contact->id)
            ->update([
                'last_message_at' => $message->created_at,
                'total_messages' => DB::raw('total_messages + 1'),
            ]);
    }

    /**
     * Create a default channel for a client if it doesn't exist
     * Uses system defaults based on channel type
     */
    private function createDefaultChannel(int $clientId, string $channelName): Channel
    {
        // Get client to access sender_id
        $client = Client::findOrFail($clientId);
        
        try {
            // Create channel based on channel type
            switch ($channelName) {
                case 'sms':
                    return $this->createDefaultSmsChannel($client);
                
                case 'email':
                    return $this->createDefaultEmailChannel($client);
                
                case 'whatsapp':
                    return $this->createDefaultWhatsAppChannel($client);
                
                default:
                    throw new \RuntimeException("Unsupported channel type: {$channelName}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to create default channel', [
                'client_id' => $clientId,
                'channel' => $channelName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException("Failed to create default {$channelName} channel: " . $e->getMessage());
        }
    }

    /**
     * Create default SMS channel using Onfon gateway
     */
    private function createDefaultSmsChannel(Client $client): Channel
    {
        // Get system Onfon credentials from config
        $onfonConfig = config('sms.gateways.onfon', []);
        
        // Create or update SMS channel
        $channel = Channel::firstOrCreate(
            [
                'client_id' => $client->id,
                'name' => 'sms',
                'provider' => 'onfon',
            ],
            [
                'credentials' => [
                    'api_key' => $onfonConfig['api_key'] ?? env('ONFON_API_KEY', ''),
                    'client_id' => $onfonConfig['client_id'] ?? env('ONFON_CLIENT_ID', ''),
                    'access_key_header' => env('ONFON_ACCESS_KEY_HEADER', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB'),
                    'default_sender' => $client->sender_id,
                    'base_url' => $onfonConfig['url'] ?? 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS',
                ],
                'active' => true,
                'config' => [
                    'uses_system_gateway' => true,
                    'auto_created' => true,
                ],
            ]
        );

        // Ensure channel is active
        if (!$channel->active) {
            $channel->update(['active' => true]);
        }

        Log::info('Default SMS channel created/updated for client', [
            'client_id' => $client->id,
            'channel_id' => $channel->id,
            'sender_id' => $client->sender_id,
        ]);

        return $channel->fresh();
    }

    /**
     * Create default Email channel using SMTP
     */
    private function createDefaultEmailChannel(Client $client): Channel
    {
        $channel = Channel::firstOrCreate(
            [
                'client_id' => $client->id,
                'name' => 'email',
                'provider' => 'smtp',
            ],
            [
                'credentials' => [
                    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
                    'port' => env('MAIL_PORT', 587),
                    'username' => env('MAIL_USERNAME', ''),
                    'password' => env('MAIL_PASSWORD', ''),
                    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                    'from_email' => $client->contact ?? env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
                    'from_name' => $client->company_name ?? $client->name,
                ],
                'active' => true,
                'config' => [
                    'uses_system_gateway' => true,
                    'auto_created' => true,
                ],
            ]
        );

        if (!$channel->active) {
            $channel->update(['active' => true]);
        }

        return $channel->fresh();
    }

    /**
     * Create default WhatsApp channel
     */
    private function createDefaultWhatsAppChannel(Client $client): Channel
    {
        // For WhatsApp, you might want to use a default provider
        // This is a placeholder - adjust based on your WhatsApp setup
        $channel = Channel::firstOrCreate(
            [
                'client_id' => $client->id,
                'name' => 'whatsapp',
                'provider' => 'ultramsg', // or your default WhatsApp provider
            ],
            [
                'credentials' => [
                    'instance_id' => env('ULTRAMSG_INSTANCE_ID', ''),
                    'token' => env('ULTRAMSG_TOKEN', ''),
                ],
                'active' => false, // Default to inactive - needs manual configuration
                'config' => [
                    'auto_created' => true,
                    'requires_configuration' => true,
                ],
            ]
        );

        return $channel->fresh();
    }
}


