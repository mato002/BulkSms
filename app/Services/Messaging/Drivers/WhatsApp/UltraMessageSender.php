<?php

namespace App\Services\Messaging\Drivers\WhatsApp;

use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UltraMessageSender implements MessageSender
{
    private string $instanceId;
    private string $token;
    private string $baseUrl;

    public function __construct(private readonly array $credentials)
    {
        $this->instanceId = $credentials['instance_id'] ?? '';
        $this->token = $credentials['token'] ?? '';
        $this->baseUrl = "https://api.ultramsg.com/{$this->instanceId}";
    }

    public function send(OutboundMessage $message): string
    {
        // Validate credentials
        if (empty($this->instanceId) || empty($this->token)) {
            throw new \RuntimeException('UltraMsg credentials not configured');
        }

        // Clean recipient number (UltraMsg accepts with or without +)
        $recipient = $this->formatPhoneNumber($message->recipient);

        // Determine message type and build payload
        $endpoint = $this->getEndpoint($message);
        $payload = $this->buildPayload($message, $recipient);

        try {
            // For Windows environments, we may need to disable SSL verification
            // In production, you should use a proper CA certificate bundle
            $httpClient = Http::timeout(30);
            
            // On Windows, set SSL certificate verification options
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $httpClient = $httpClient->withOptions([
                    'verify' => false // Disable SSL verification on Windows
                ]);
            }
            
            $response = $httpClient->post("{$this->baseUrl}/{$endpoint}", array_merge($payload, [
                'token' => $this->token
            ]));

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('UltraMsg API Error', [
                    'status' => $response->status(),
                    'error' => $error,
                    'recipient' => $recipient
                ]);
                throw new \RuntimeException(
                    $error['error'] ?? $error['message'] ?? 'UltraMsg API request failed'
                );
            }

            $result = $response->json();
            
            Log::info('UltraMsg API Response', [
                'recipient' => $recipient,
                'response' => $result
            ]);
            
            // Check if message was sent successfully
            if (isset($result['sent']) && ($result['sent'] === 'true' || $result['sent'] === true)) {
                return $result['id'] ?? 'ultra_' . uniqid();
            }

            // Log failure details
            Log::warning('UltraMsg message not sent', [
                'recipient' => $recipient,
                'response' => $result
            ]);

            throw new \RuntimeException($result['error'] ?? $result['message'] ?? 'Message sending failed');

        } catch (\Exception $e) {
            Log::error('UltraMsg sending failed', [
                'message' => $e->getMessage(),
                'recipient' => $recipient
            ]);
            throw $e;
        }
    }

    /**
     * Get the appropriate API endpoint based on message type
     */
    private function getEndpoint(OutboundMessage $message): string
    {
        // Check message metadata for specific types
        if (isset($message->metadata['media_type'])) {
            $mediaType = $message->metadata['media_type'];
            return match($mediaType) {
                'image' => 'messages/image',
                'video' => 'messages/video',
                'audio' => 'messages/audio',
                'voice' => 'messages/voice',
                'document' => 'messages/document',
                'sticker' => 'messages/sticker',
                'contact' => 'messages/contact',
                'location' => 'messages/location',
                default => 'messages/chat'
            };
        }

        // Check for interactive messages
        if (isset($message->metadata['interactive_type'])) {
            return match($message->metadata['interactive_type']) {
                'button', 'list' => 'messages/chat', // UltraMsg doesn't have native interactive, we'll format as text
                default => 'messages/chat'
            };
        }

        // Default to chat message
        return 'messages/chat';
    }

    /**
     * Build the API payload based on message content
     */
    private function buildPayload(OutboundMessage $message, string $recipient): array
    {
        $payload = [
            'to' => $recipient
        ];

        // Handle media messages
        if (isset($message->metadata['media_type'])) {
            $mediaType = $message->metadata['media_type'];
            
            switch ($mediaType) {
                case 'image':
                    $payload['image'] = $message->metadata['media_url'] ?? $message->metadata['media_id'];
                    if (!empty($message->body)) {
                        $payload['caption'] = $message->body;
                    }
                    break;

                case 'video':
                    $payload['video'] = $message->metadata['media_url'] ?? $message->metadata['media_id'];
                    if (!empty($message->body)) {
                        $payload['caption'] = $message->body;
                    }
                    break;

                case 'audio':
                    $payload['audio'] = $message->metadata['media_url'] ?? $message->metadata['media_id'];
                    break;

                case 'voice':
                    $payload['audio'] = $message->metadata['media_url'] ?? $message->metadata['media_id'];
                    break;

                case 'document':
                    $payload['document'] = $message->metadata['media_url'] ?? $message->metadata['media_id'];
                    if (isset($message->metadata['filename'])) {
                        $payload['filename'] = $message->metadata['filename'];
                    }
                    if (!empty($message->body)) {
                        $payload['caption'] = $message->body;
                    }
                    break;

                case 'sticker':
                    $payload['sticker'] = $message->metadata['media_url'] ?? $message->metadata['media_id'];
                    break;

                case 'contact':
                    $payload['contact'] = $message->metadata['contact_data'] ?? $message->body;
                    break;

                case 'location':
                    $payload['address'] = $message->metadata['address'] ?? $message->body;
                    $payload['lat'] = $message->metadata['latitude'] ?? '';
                    $payload['lng'] = $message->metadata['longitude'] ?? '';
                    break;
            }

            return $payload;
        }

        // Handle interactive messages (convert to formatted text)
        if (isset($message->metadata['interactive_type'])) {
            $text = $this->formatInteractiveMessage($message);
            $payload['body'] = $text;
            return $payload;
        }

        // Handle link preview
        $payload['body'] = $message->body;
        
        // Enable link preview if URL detected
        if (preg_match('/https?:\/\/[^\s]+/', $message->body)) {
            $payload['preview_url'] = true;
        }

        // Add priority if specified
        if (isset($message->metadata['priority'])) {
            $payload['priority'] = $message->metadata['priority'];
        }

        // Add referenceId if specified
        if (isset($message->metadata['reference_id'])) {
            $payload['referenceId'] = $message->metadata['reference_id'];
        }

        // Mentioned users (for group messages)
        if (isset($message->metadata['mentions'])) {
            $payload['mentions'] = $message->metadata['mentions'];
        }

        return $payload;
    }

    /**
     * Format interactive message as text (UltraMsg doesn't support native buttons)
     */
    private function formatInteractiveMessage(OutboundMessage $message): string
    {
        $text = '';

        // Add header if present
        if (isset($message->metadata['header'])) {
            $text .= "*{$message->metadata['header']}*\n\n";
        }

        // Add body
        $text .= $message->body . "\n\n";

        // Add buttons as numbered list
        if ($message->metadata['interactive_type'] === 'button' && isset($message->metadata['buttons'])) {
            $text .= "📌 *Options:*\n";
            foreach ($message->metadata['buttons'] as $index => $button) {
                $title = $button['reply']['title'] ?? $button['title'] ?? "Option " . ($index + 1);
                $text .= ($index + 1) . ". {$title}\n";
            }
        }

        // Add list sections
        if ($message->metadata['interactive_type'] === 'list' && isset($message->metadata['sections'])) {
            $text .= "📋 *Please select:*\n\n";
            foreach ($message->metadata['sections'] as $section) {
                if (isset($section['title'])) {
                    $text .= "*{$section['title']}*\n";
                }
                foreach ($section['rows'] ?? [] as $index => $row) {
                    $title = $row['title'] ?? "Option " . ($index + 1);
                    $description = isset($row['description']) ? " - {$row['description']}" : "";
                    $text .= "• {$title}{$description}\n";
                }
                $text .= "\n";
            }
        }

        // Add footer if present
        if (isset($message->metadata['footer'])) {
            $text .= "\n_{$message->metadata['footer']}_";
        }

        return trim($text);
    }

    /**
     * Format phone number for UltraMsg API
     */
    private function formatPhoneNumber(string $phone): string
    {
        return PhoneNumber::e164($phone);
    }

    public function channel(): string
    {
        return 'whatsapp';
    }

    public function provider(): string
    {
        return 'ultramsg';
    }
}

