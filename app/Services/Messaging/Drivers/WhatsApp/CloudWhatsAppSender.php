<?php

namespace App\Services\Messaging\Drivers\WhatsApp;

use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudWhatsAppSender implements MessageSender
{
    private string $phoneNumberId;
    private string $accessToken;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct(private readonly array $credentials)
    {
        $this->phoneNumberId = $credentials['phone_number_id'] ?? '';
        $this->accessToken = $credentials['access_token'] ?? '';
        $this->apiVersion = $credentials['api_version'] ?? 'v21.0';
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }

    public function send(OutboundMessage $message): string
    {
        // Validate credentials
        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            throw new \RuntimeException('WhatsApp Cloud API credentials not configured');
        }

        // Clean recipient number (remove + and ensure format is correct)
        $recipient = $this->formatPhoneNumber($message->recipient);

        // Determine message type and build payload
        $payload = $this->buildPayload($message, $recipient);

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(30)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('WhatsApp API Error', [
                    'status' => $response->status(),
                    'error' => $error,
                    'recipient' => $recipient
                ]);
                throw new \RuntimeException(
                    $error['error']['message'] ?? 'WhatsApp API request failed'
                );
            }

            $result = $response->json();
            return $result['messages'][0]['id'] ?? 'wacloud_' . bin2hex(random_bytes(6));

        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed', [
                'message' => $e->getMessage(),
                'recipient' => $recipient
            ]);
            throw $e;
        }
    }

    /**
     * Build the API payload based on message content
     */
    private function buildPayload(OutboundMessage $message, string $recipient): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
        ];

        // Check if this is a template message
        if (isset($message->metadata['template_name'])) {
            $payload['type'] = 'template';
            $payload['template'] = $this->buildTemplatePayload($message);
        }
        // Check if this is an interactive message (buttons/list)
        elseif (isset($message->metadata['interactive_type'])) {
            $payload['type'] = 'interactive';
            $payload['interactive'] = $this->buildInteractivePayload($message);
        }
        // Check if this is a media message
        elseif (isset($message->metadata['media_type'])) {
            $mediaType = $message->metadata['media_type'];
            $payload['type'] = $mediaType;
            $payload[$mediaType] = $this->buildMediaPayload($message);
        }
        // Default: text message
        else {
            $payload['type'] = 'text';
            $payload['text'] = [
                'preview_url' => true,
                'body' => $message->body
            ];
        }

        return $payload;
    }

    /**
     * Build template message payload
     */
    private function buildTemplatePayload(OutboundMessage $message): array
    {
        $template = [
            'name' => $message->metadata['template_name'],
            'language' => [
                'code' => $message->metadata['language_code'] ?? 'en'
            ]
        ];

        // Add template components (header, body, buttons)
        if (isset($message->metadata['template_components'])) {
            $template['components'] = $message->metadata['template_components'];
        }

        return $template;
    }

    /**
     * Build interactive message payload (buttons or list)
     */
    private function buildInteractivePayload(OutboundMessage $message): array
    {
        $type = $message->metadata['interactive_type']; // 'button' or 'list'
        
        $interactive = [
            'type' => $type,
            'body' => [
                'text' => $message->body
            ]
        ];

        // Add header if provided
        if (isset($message->metadata['header'])) {
            $interactive['header'] = [
                'type' => 'text',
                'text' => $message->metadata['header']
            ];
        }

        // Add footer if provided
        if (isset($message->metadata['footer'])) {
            $interactive['footer'] = [
                'text' => $message->metadata['footer']
            ];
        }

        // Add buttons or list action
        if ($type === 'button') {
            $interactive['action'] = [
                'buttons' => $message->metadata['buttons'] ?? []
            ];
        } elseif ($type === 'list') {
            $interactive['action'] = [
                'button' => $message->metadata['action_button'] ?? 'Select',
                'sections' => $message->metadata['sections'] ?? []
            ];
        }

        return $interactive;
    }

    /**
     * Build media message payload
     */
    private function buildMediaPayload(OutboundMessage $message): array
    {
        $mediaType = $message->metadata['media_type'];
        $media = [];

        // Media can be sent via URL or ID
        if (isset($message->metadata['media_id'])) {
            $media['id'] = $message->metadata['media_id'];
        } elseif (isset($message->metadata['media_url'])) {
            $media['link'] = $message->metadata['media_url'];
        }

        // Add caption for image, video, document
        if (in_array($mediaType, ['image', 'video', 'document']) && !empty($message->body)) {
            $media['caption'] = $message->body;
        }

        // Add filename for documents
        if ($mediaType === 'document' && isset($message->metadata['filename'])) {
            $media['filename'] = $message->metadata['filename'];
        }

        return $media;
    }

    /**
     * Format phone number for WhatsApp API
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Remove leading + if present
        $phone = ltrim($phone, '+');
        
        // Ensure it has country code (add default if needed)
        // You might want to configure a default country code
        if (strlen($phone) < 10) {
            throw new \InvalidArgumentException('Invalid phone number format');
        }
        
        return $phone;
    }

    public function channel(): string
    {
        return 'whatsapp';
    }

    public function provider(): string
    {
        return 'whatsapp_cloud';
    }
}



