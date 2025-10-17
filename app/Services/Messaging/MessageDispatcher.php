<?php

namespace App\Services\Messaging;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Message;
use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\UrlShortenerService;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class MessageDispatcher
{
    public function __construct(private readonly Encrypter $encrypter)
    {
    }

    public function dispatch(OutboundMessage $outbound): Message
    {
        $channelConfig = Channel::query()
            ->where('client_id', $outbound->clientId)
            ->where('name', $outbound->channel)
            ->where('active', true)
            ->firstOrFail();

        // Handle credentials - can be string (JSON) or already an array
        $credentials = $channelConfig->credentials;
        if (is_string($credentials)) {
            $credentials = (array) json_decode($credentials ?? '{}', true);
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
}


