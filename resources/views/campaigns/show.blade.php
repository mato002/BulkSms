@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Campaign: {{ $campaign->name }}</h1>
        <div>
            @if($campaign->status === 'draft')
                <form action="{{ route('campaigns.send', $campaign->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Send this campaign now?')">Send Campaign</button>
                </form>
            @endif
            <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Campaign Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Channel:</strong> 
                        @if(($campaign->channel ?? 'sms') === 'whatsapp')
                            <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                        @else
                            <span class="badge bg-primary"><i class="bi bi-chat-dots"></i> SMS</span>
                        @endif
                    </p>
                    <p><strong>Status:</strong> 
                        @if($campaign->status === 'sent')
                            <span class="badge bg-success">Sent</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($campaign->status) }}</span>
                        @endif
                    </p>
                    <p><strong>Sender ID:</strong> {{ $campaign->sender_id ?? '-' }}</p>
                    <p><strong>Total Recipients:</strong> {{ $campaign->total_recipients }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Sent Count:</strong> {{ $campaign->sent_count ?? 0 }}</p>
                    <p><strong>Failed Count:</strong> {{ $campaign->failed_count ?? 0 }}</p>
                    <p><strong>Created:</strong> {{ \Carbon\Carbon::parse($campaign->created_at)->format('M d, Y H:i') }}</p>
                    @if($campaign->sent_at)
                        <p><strong>Sent At:</strong> {{ \Carbon\Carbon::parse($campaign->sent_at)->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Message</h5>
        </div>
        <div class="card-body">
            <pre class="mb-0">{{ $campaign->message }}</pre>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recipients ({{ $campaign->total_recipients }})</h5>
        </div>
        <div class="card-body">
            <div style="max-height: 300px; overflow-y: auto;">
                @php
                    $recipients = json_decode($campaign->recipients, true) ?? [];
                @endphp
                <ul class="list-unstyled mb-0">
                    @foreach($recipients as $recipient)
                        <li class="py-1">{{ $recipient }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

