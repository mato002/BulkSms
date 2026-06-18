<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly MessageDispatcher $dispatcher)
    {
    }

    public function send(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => ['required','integer','exists:clients,id'],
                'channel' => ['required','in:sms,whatsapp,email'],
                'recipient' => ['required','string'],
                'sender' => ['nullable','string'],
                'subject' => ['nullable','string'],
                'body' => ['required','string'],
                'template_id' => ['nullable','integer','exists:templates,id'],
                'metadata' => ['nullable','array'],
                'append_reply_link' => ['nullable','boolean'],
                'append_stop_footer' => ['nullable','boolean'],
            ]);

            $metadata = $validated['metadata'] ?? [];
            if (array_key_exists('append_reply_link', $validated)) {
                $metadata['append_reply_link'] = $validated['append_reply_link'];
            }
            if (array_key_exists('append_stop_footer', $validated)) {
                $metadata['append_stop_footer'] = $validated['append_stop_footer'];
            }

            $outbound = new OutboundMessage(
                clientId: (int) $validated['client_id'],
                channel: $validated['channel'],
                recipient: $validated['recipient'],
                sender: $validated['sender'] ?? null,
                subject: $validated['subject'] ?? null,
                body: $validated['body'],
                templateId: $validated['template_id'] ?? null,
                metadata: $metadata,
            );

            $message = $this->dispatcher->dispatch($outbound);

            $client = \App\Models\Client::find($validated['client_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'status' => $message->status,
                    'provider_message_id' => $message->provider_message_id,
                    'cost' => (float) ($message->cost ?? 0),
                    'segments' => $message->metadata['segments'] ?? null,
                    'balance_remaining' => $client ? (float) $client->fresh()->balance : null,
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('API Message Send Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }
}



