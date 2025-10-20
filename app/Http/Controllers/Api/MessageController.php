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
            ]);

            $outbound = new OutboundMessage(
                clientId: (int) $validated['client_id'],
                channel: $validated['channel'],
                recipient: $validated['recipient'],
                sender: $validated['sender'] ?? null,
                subject: $validated['subject'] ?? null,
                body: $validated['body'],
                templateId: $validated['template_id'] ?? null,
                metadata: $validated['metadata'] ?? [],
            );

            $message = $this->dispatcher->dispatch($outbound);

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'status' => $message->status,
                    'provider_message_id' => $message->provider_message_id,
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



