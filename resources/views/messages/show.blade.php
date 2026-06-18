@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-envelope-open me-2"></i>Message #{{ $message->id }}</h1>
            <p class="text-muted mb-0">
                @if($client)
                    {{ $client->name }} (Client #{{ $client->id }})
                @endif
                · {{ \Carbon\Carbon::parse($message->created_at)->format('M d, Y H:i:s') }}
            </p>
        </div>
        <a href="{{ $returnUrl ?? route('messages.all') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Message Body</h5>
                    @if($message->direction ?? null)
                        <span class="badge {{ ($message->direction ?? '') === 'inbound' ? 'bg-info' : 'bg-primary' }}">
                            {{ ucfirst($message->direction ?? 'outbound') }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <pre class="mb-0 message-body-pre">{{ $message->body }}</pre>
                    @if($segments)
                        <div class="mt-3 pt-3 border-top small text-muted">
                            <strong>{{ $segments['length'] }}</strong> characters
                            · <strong>{{ $segments['segments'] }}</strong> SMS part(s)
                            · {{ $segments['encoding'] }}
                            @if($segments['segments'] > 1)
                                <span class="text-warning">(multi-part message)</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($message->subject)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Subject</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $message->subject }}</p>
                </div>
            </div>
            @endif

            @if($message->error_message)
            <div class="card shadow-sm mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Error Details</h5>
                </div>
                <div class="card-body">
                    @if($message->error_code ?? null)
                        <p><strong>Error Code:</strong> {{ $message->error_code }}</p>
                    @endif
                    <pre class="mb-0 text-danger">{{ $message->error_message }}</pre>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                @if(in_array($message->status, ['sent', 'delivered']))
                                    <span class="badge bg-success">{{ ucfirst($message->status) }}</span>
                                @elseif($message->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($message->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Channel</td>
                            <td class="text-end"><span class="badge bg-secondary">{{ strtoupper($message->channel) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sender</td>
                            <td class="text-end fw-semibold">{{ $message->sender ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Recipient</td>
                            <td class="text-end fw-semibold font-monospace">{{ $message->recipient }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Provider</td>
                            <td class="text-end">{{ $message->provider ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Cost</td>
                            <td class="text-end fw-semibold">
                                KES {{ number_format((float) ($message->cost ?? 0), 2) }}
                                @if($segments && $segments['segments'] > 1 && ($message->cost ?? 0) > 0)
                                    <div class="small text-muted fw-normal">
                                        {{ $segments['segments'] }} parts
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @if($message->provider_message_id ?? null)
                        <tr>
                            <td class="text-muted">Provider ID</td>
                            <td class="text-end font-monospace small">{{ $message->provider_message_id }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end small">{{ \Carbon\Carbon::parse($message->created_at)->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @if($message->sent_at ?? null)
                        <tr>
                            <td class="text-muted">Sent</td>
                            <td class="text-end small">{{ \Carbon\Carbon::parse($message->sent_at)->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($message->delivered_at ?? null)
                        <tr>
                            <td class="text-muted">Delivered</td>
                            <td class="text-end small">{{ \Carbon\Carbon::parse($message->delivered_at)->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($message->failed_at ?? null)
                        <tr>
                            <td class="text-muted">Failed</td>
                            <td class="text-end small">{{ \Carbon\Carbon::parse($message->failed_at)->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($client && auth()->user()->isAdmin())
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('admin.senders.show', $client->id) }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-building me-2"></i>View Sender ({{ $client->sender_id }})
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.message-body-pre {
    white-space: pre-wrap;
    word-break: break-word;
    font-family: inherit;
    font-size: 0.95rem;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
}
</style>
@endsection
