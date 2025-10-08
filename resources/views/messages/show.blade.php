@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Message Details</h1>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Message Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Message ID:</strong> {{ $message->id }}</p>
                    <p><strong>Channel:</strong> <span class="badge bg-secondary">{{ strtoupper($message->channel) }}</span></p>
                    <p><strong>Provider:</strong> {{ $message->provider ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        @if($message->status === 'sent' || $message->status === 'delivered')
                            <span class="badge bg-success">{{ ucfirst($message->status) }}</span>
                        @elseif($message->status === 'failed')
                            <span class="badge bg-danger">Failed</span>
                        @else
                            <span class="badge bg-warning">{{ ucfirst($message->status) }}</span>
                        @endif
                    </p>
                    <p><strong>Provider Message ID:</strong> {{ $message->provider_message_id ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Sender:</strong> {{ $message->sender }}</p>
                    <p><strong>Recipient:</strong> {{ $message->recipient }}</p>
                    <p><strong>Cost:</strong> ${{ number_format($message->cost, 4) }}</p>
                    <p><strong>Created:</strong> {{ \Carbon\Carbon::parse($message->created_at)->format('M d, Y H:i:s') }}</p>
                    @if($message->sent_at)
                        <p><strong>Sent:</strong> {{ \Carbon\Carbon::parse($message->sent_at)->format('M d, Y H:i:s') }}</p>
                    @endif
                    @if($message->delivered_at)
                        <p><strong>Delivered:</strong> {{ \Carbon\Carbon::parse($message->delivered_at)->format('M d, Y H:i:s') }}</p>
                    @endif
                    @if($message->failed_at)
                        <p><strong>Failed:</strong> {{ \Carbon\Carbon::parse($message->failed_at)->format('M d, Y H:i:s') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($message->subject)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Subject</h5>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $message->subject }}</p>
        </div>
    </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Message Body</h5>
        </div>
        <div class="card-body">
            <pre class="mb-0">{{ $message->body }}</pre>
        </div>
    </div>

    @if($message->error_message)
    <div class="card mb-3">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Error Details</h5>
        </div>
        <div class="card-body">
            <p><strong>Error Code:</strong> {{ $message->error_code ?? 'N/A' }}</p>
            <pre class="mb-0 text-danger">{{ $message->error_message }}</pre>
        </div>
    </div>
    @endif

    <a href="{{ route('messages.index') }}" class="btn btn-secondary">Back to Messages</a>
</div>
@endsection

