<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification as CustomNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Webhook verification (required by WhatsApp)
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token
        ]);

        return response('Verification failed', 403);
    }

    /**
     * Handle incoming webhook events
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('WhatsApp webhook received', ['payload' => $payload]);

        try {
            // Detect webhook type (UltraMsg or Cloud API)
            if (isset($payload['data']) && isset($payload['event'])) {
                // UltraMsg webhook format
                return $this->handleUltraMsg($payload);
            }

            // WhatsApp Cloud API webhook format
            if (!isset($payload['entry'])) {
                return response()->json(['status' => 'ok']);
            }

            foreach ($payload['entry'] as $entry) {
                if (!isset($entry['changes'])) {
                    continue;
                }

                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        $this->handleMessagesChange($change['value']);
                    }
                }
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            // Always return 200 to prevent retrying
            return response()->json(['status' => 'ok']);
        }
    }

    /**
     * Handle UltraMsg webhook
     */
    private function handleUltraMsg(array $payload)
    {
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        Log::info('UltraMsg webhook event', ['event' => $event, 'data' => $data]);

        // Handle different UltraMsg events
        switch ($event) {
            case 'message':
            case 'message.new':
                $this->handleUltraMsgMessage($data);
                break;

            case 'message.ack':
            case 'message.status':
                $this->handleUltraMsgStatus($data);
                break;

            default:
                Log::info('Unhandled UltraMsg event', ['event' => $event]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle UltraMsg incoming message
     */
    private function handleUltraMsgMessage(array $data)
    {
        // Find channel by instance_id (from data or config)
        $from = $data['from'] ?? null;
        $messageId = $data['id'] ?? $data['messageId'] ?? null;
        $body = $data['body'] ?? '';
        $type = $data['type'] ?? 'chat';

        if (!$from || !$messageId) {
            Log::warning('Invalid UltraMsg message data', ['data' => $data]);
            return;
        }

        // Find the channel (UltraMsg provider)
        $channel = Channel::where('provider', 'ultramsg')
            ->where('active', true)
            ->first();

        if (!$channel) {
            Log::warning('No active UltraMsg channel found');
            return;
        }

        // Extract message content based on type
        $content = $this->extractUltraMsgContent($data);

        // Find or create contact
        $contact = Contact::firstOrCreate(
            [
                'client_id' => $channel->client_id,
                'phone' => $from
            ],
            [
                'name' => $data['pushname'] ?? $data['notifyName'] ?? $from,
                'email' => null,
                'source' => 'whatsapp'
            ]
        );

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'client_id' => $channel->client_id,
                'contact_id' => $contact->id,
                'channel' => 'whatsapp'
            ],
            [
                'status' => 'open',
                'last_message_at' => now()
            ]
        );

        // Create inbound message
        $message = new Message();
        $message->client_id = $channel->client_id;
        $message->conversation_id = $conversation->id;
        $message->contact_id = $contact->id;
        $message->channel = 'whatsapp';
        $message->provider = 'ultramsg';
        $message->direction = 'inbound';
        $message->sender = $from;
        $message->recipient = $data['to'] ?? '';
        $message->body = $content['body'];
        $message->status = 'received';
        $message->provider_message_id = $messageId;
        $message->is_read = false;
        $message->sent_at = isset($data['time']) ? date('Y-m-d H:i:s', $data['time']) : now();
        $message->metadata = $content['metadata'];
        $message->save();

        // Update conversation
        $conversation->last_message_at = now();
        $conversation->last_message_preview = substr($content['body'], 0, 100);
        $conversation->unread_count = ($conversation->unread_count ?? 0) + 1;
        $conversation->save();

        // Update contact
        $contact->last_contact_at = now();
        $contact->conversation_id = $conversation->id;
        $contact->save();

        Log::info('UltraMsg message processed', [
            'message_id' => $messageId,
            'contact_id' => $contact->id,
            'conversation_id' => $conversation->id
        ]);
    }

    /**
     * Handle UltraMsg message status
     */
    private function handleUltraMsgStatus(array $data)
    {
        $messageId = $data['id'] ?? $data['messageId'] ?? null;
        $status = $data['status'] ?? $data['ack'] ?? null;

        if (!$messageId) {
            return;
        }

        // Find message by provider_message_id
        $message = Message::where('provider_message_id', $messageId)->first();

        if (!$message) {
            Log::warning('Message not found for status update', ['message_id' => $messageId]);
            return;
        }

        // Map UltraMsg status to our status
        $newStatus = match($status) {
            'sent', '1' => 'sent',
            'delivered', '2' => 'delivered',
            'read', '3' => 'read',
            'failed', 'error' => 'failed',
            default => $message->status
        };

        if ($newStatus !== $message->status) {
            $message->status = $newStatus;
            
            switch ($newStatus) {
                case 'sent':
                    $message->sent_at = now();
                    break;
                case 'delivered':
                    $message->delivered_at = now();
                    break;
                case 'read':
                    $message->read_at = now();
                    break;
                case 'failed':
                    $message->failed_at = now();
                    $message->error_message = $data['error'] ?? 'Message delivery failed';
                    break;
            }

            $message->save();
            Log::info('UltraMsg status updated', ['message_id' => $messageId, 'status' => $newStatus]);
        }
    }

    /**
     * Extract content from UltraMsg message
     */
    private function extractUltraMsgContent(array $data): array
    {
        $type = $data['type'] ?? 'chat';
        $body = '';
        $metadata = ['type' => $type];

        switch ($type) {
            case 'chat':
            case 'text':
                $body = $data['body'] ?? '';
                break;

            case 'image':
                $body = '[Image]';
                $metadata['media_url'] = $data['body'] ?? $data['url'] ?? '';
                $metadata['caption'] = $data['caption'] ?? '';
                $metadata['mime_type'] = $data['mimetype'] ?? 'image/jpeg';
                if ($metadata['caption']) {
                    $body = $metadata['caption'];
                }
                break;

            case 'video':
                $body = '[Video]';
                $metadata['media_url'] = $data['body'] ?? $data['url'] ?? '';
                $metadata['caption'] = $data['caption'] ?? '';
                $metadata['mime_type'] = $data['mimetype'] ?? 'video/mp4';
                if ($metadata['caption']) {
                    $body = $metadata['caption'];
                }
                break;

            case 'audio':
            case 'ptt':
            case 'voice':
                $body = '[Audio]';
                $metadata['media_url'] = $data['body'] ?? $data['url'] ?? '';
                $metadata['mime_type'] = $data['mimetype'] ?? 'audio/ogg';
                break;

            case 'document':
                $body = '[Document]';
                $metadata['media_url'] = $data['body'] ?? $data['url'] ?? '';
                $metadata['filename'] = $data['filename'] ?? 'document';
                $metadata['caption'] = $data['caption'] ?? '';
                $metadata['mime_type'] = $data['mimetype'] ?? 'application/pdf';
                if ($metadata['caption']) {
                    $body = $metadata['caption'];
                }
                break;

            case 'location':
                $lat = $data['latitude'] ?? $data['lat'] ?? '';
                $lng = $data['longitude'] ?? $data['lng'] ?? '';
                $body = "[Location: {$lat}, {$lng}]";
                $metadata['latitude'] = $lat;
                $metadata['longitude'] = $lng;
                $metadata['address'] = $data['address'] ?? '';
                break;

            case 'vcard':
            case 'contact':
                $body = '[Contact Card]';
                $metadata['vcard'] = $data['body'] ?? $data['vcard'] ?? '';
                break;

            case 'sticker':
                $body = '[Sticker]';
                $metadata['media_url'] = $data['body'] ?? $data['url'] ?? '';
                break;

            default:
                $body = $data['body'] ?? "[Unsupported: {$type}]";
                $metadata['raw_data'] = $data;
        }

        return [
            'body' => $body,
            'metadata' => $metadata
        ];
    }

    /**
     * Handle messages change event
     */
    private function handleMessagesChange(array $value)
    {
        // Handle status updates
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleStatusUpdate($status);
            }
        }

        // Handle incoming messages
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage($value, $message);
            }
        }
    }

    /**
     * Handle status update for sent messages
     */
    private function handleStatusUpdate(array $status)
    {
        $messageId = $status['id'];
        $newStatus = $status['status']; // sent, delivered, read, failed

        Log::info('WhatsApp status update', [
            'message_id' => $messageId,
            'status' => $newStatus
        ]);

        // Find the message by provider_message_id
        $message = Message::where('provider_message_id', $messageId)->first();

        if (!$message) {
            Log::warning('Message not found for status update', ['message_id' => $messageId]);
            return;
        }

        // Update message status
        switch ($newStatus) {
            case 'sent':
                $message->status = 'sent';
                $message->sent_at = now();
                break;
            case 'delivered':
                $message->status = 'delivered';
                $message->delivered_at = now();
                break;
            case 'read':
                $message->status = 'read';
                $message->read_at = now();
                break;
            case 'failed':
                $message->status = 'failed';
                $message->failed_at = now();
                if (isset($status['errors'])) {
                    $message->error_message = json_encode($status['errors']);
                }
                break;
        }

        $message->save();
    }

    /**
     * Handle incoming message from customer
     */
    private function handleIncomingMessage(array $value, array $messageData)
    {
        $phoneNumberId = $value['metadata']['phone_number_id'];
        $from = $messageData['from'];
        $messageId = $messageData['id'];
        $timestamp = $messageData['timestamp'];

        // Find the channel by phone_number_id
        $channel = Channel::where('provider', 'whatsapp_cloud')
            ->where('active', true)
            ->get()
            ->first(function ($ch) use ($phoneNumberId) {
                $credentials = json_decode($ch->credentials, true);
                return isset($credentials['phone_number_id']) && 
                       $credentials['phone_number_id'] === $phoneNumberId;
            });

        if (!$channel) {
            Log::warning('No WhatsApp channel found for phone_number_id', [
                'phone_number_id' => $phoneNumberId
            ]);
            return;
        }

        // Extract message content
        $content = $this->extractMessageContent($messageData);

        // Find or create contact
        $contact = Contact::firstOrCreate(
            [
                'client_id' => $channel->client_id,
                'phone' => $from
            ],
            [
                'name' => $value['contacts'][0]['profile']['name'] ?? $from,
                'email' => null,
                'source' => 'whatsapp'
            ]
        );

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'client_id' => $channel->client_id,
                'contact_id' => $contact->id,
                'channel' => 'whatsapp'
            ],
            [
                'status' => 'open',
                'last_message_at' => now()
            ]
        );

        // Create inbound message
        $message = new Message();
        $message->client_id = $channel->client_id;
        $message->conversation_id = $conversation->id;
        $message->contact_id = $contact->id;
        $message->channel = 'whatsapp';
        $message->provider = 'whatsapp_cloud';
        $message->direction = 'inbound';
        $message->sender = $from;
        $message->recipient = $phoneNumberId;
        $message->body = $content['body'];
        $message->status = 'received';
        $message->provider_message_id = $messageId;
        $message->is_read = false;
        $message->sent_at = date('Y-m-d H:i:s', $timestamp);
        $message->metadata = $content['metadata'];
        $message->save();

        // Update conversation
        $conversation->last_message_at = now();
        $conversation->last_message_preview = substr($content['body'], 0, 100);
        $conversation->unread_count = ($conversation->unread_count ?? 0) + 1;
        $conversation->save();

        // Update contact
        $contact->last_contact_at = now();
        $contact->conversation_id = $conversation->id;
        $contact->save();

        // Create notification for new message
        try {
            CustomNotification::newMessage(
                $channel->client_id,
                $contact->name,
                $content['body'],
                $conversation->id,
                'whatsapp'
            );
        } catch (\Exception $e) {
            Log::warning('Failed to create message notification', ['error' => $e->getMessage()]);
        }

        Log::info('WhatsApp inbound message processed', [
            'message_id' => $messageId,
            'contact_id' => $contact->id,
            'conversation_id' => $conversation->id
        ]);
    }

    /**
     * Extract message content based on message type
     */
    private function extractMessageContent(array $messageData): array
    {
        $type = $messageData['type'];
        $body = '';
        $metadata = ['type' => $type];

        switch ($type) {
            case 'text':
                $body = $messageData['text']['body'];
                break;

            case 'image':
                $body = '[Image]';
                $metadata['media_id'] = $messageData['image']['id'];
                $metadata['mime_type'] = $messageData['image']['mime_type'];
                $metadata['sha256'] = $messageData['image']['sha256'];
                if (isset($messageData['image']['caption'])) {
                    $body = $messageData['image']['caption'];
                }
                break;

            case 'video':
                $body = '[Video]';
                $metadata['media_id'] = $messageData['video']['id'];
                $metadata['mime_type'] = $messageData['video']['mime_type'];
                $metadata['sha256'] = $messageData['video']['sha256'];
                if (isset($messageData['video']['caption'])) {
                    $body = $messageData['video']['caption'];
                }
                break;

            case 'audio':
                $body = '[Audio]';
                $metadata['media_id'] = $messageData['audio']['id'];
                $metadata['mime_type'] = $messageData['audio']['mime_type'];
                $metadata['sha256'] = $messageData['audio']['sha256'];
                break;

            case 'document':
                $body = '[Document]';
                $metadata['media_id'] = $messageData['document']['id'];
                $metadata['mime_type'] = $messageData['document']['mime_type'];
                $metadata['filename'] = $messageData['document']['filename'] ?? 'document';
                $metadata['sha256'] = $messageData['document']['sha256'];
                if (isset($messageData['document']['caption'])) {
                    $body = $messageData['document']['caption'];
                }
                break;

            case 'location':
                $location = $messageData['location'];
                $body = "[Location: {$location['latitude']}, {$location['longitude']}]";
                $metadata['latitude'] = $location['latitude'];
                $metadata['longitude'] = $location['longitude'];
                if (isset($location['name'])) {
                    $metadata['name'] = $location['name'];
                }
                if (isset($location['address'])) {
                    $metadata['address'] = $location['address'];
                }
                break;

            case 'contacts':
                $body = '[Contact Card]';
                $metadata['contacts'] = $messageData['contacts'];
                break;

            case 'button':
                $body = $messageData['button']['text'];
                $metadata['button_payload'] = $messageData['button']['payload'];
                break;

            case 'interactive':
                $interactive = $messageData['interactive'];
                $body = $interactive['type'] === 'button_reply' 
                    ? $interactive['button_reply']['title']
                    : $interactive['list_reply']['title'];
                $metadata['interactive_type'] = $interactive['type'];
                $metadata['interactive_data'] = $interactive;
                break;

            default:
                $body = "[Unsupported message type: {$type}]";
                $metadata['raw_data'] = $messageData;
        }

        return [
            'body' => $body,
            'metadata' => $metadata
        ];
    }
}

