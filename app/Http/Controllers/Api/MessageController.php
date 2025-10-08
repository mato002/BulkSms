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
            'id' => $message->id,
            'status' => $message->status,
            'provider_message_id' => $message->provider_message_id,
        ], 202);
    }
}



