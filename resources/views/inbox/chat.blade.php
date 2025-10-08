@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1400px;">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Chat Area -->
        <div class="col-md-12">
            <div class="card" style="height: calc(100vh - 200px); display: flex; flex-direction: column;">
                <!-- Chat Header -->
                <div class="card-header bg-white border-bottom" style="flex-shrink: 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('inbox.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-weight: bold;">
                                {{ strtoupper(substr($conversation->contact_name, 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $conversation->contact_name }}</h5>
                                <small class="text-muted">
                                    {{ $conversation->contact_phone }}
                                    @if($conversation->department)
                                        • {{ $conversation->department }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-secondary">{{ strtoupper($conversation->channel) }}</span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Status: {{ ucfirst($conversation->status) }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="open">
                                            <button type="submit" class="dropdown-item">Mark as Open</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="dropdown-item">Mark as Resolved</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="archived">
                                            <button type="submit" class="dropdown-item">Archive</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="card-body" id="messagesContainer" style="flex: 1; overflow-y: auto; background: #f8f9fa;">
                    @forelse($messages as $msg)
                        <div class="mb-3 d-flex {{ $msg->direction === 'outbound' ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="message-bubble {{ $msg->direction === 'outbound' ? 'bg-primary text-white' : 'bg-white' }}" style="max-width: 70%; padding: 12px 16px; border-radius: 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                <div class="message-body">{{ $msg->body }}</div>
                                <div class="message-meta mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                    {{ \Carbon\Carbon::parse($msg->created_at)->format('M d, Y H:i') }}
                                    @if($msg->direction === 'outbound')
                                        @if($msg->status === 'delivered')
                                            <i class="bi bi-check-all ms-1" title="Delivered"></i>
                                        @elseif($msg->status === 'sent')
                                            <i class="bi bi-check ms-1" title="Sent"></i>
                                        @elseif($msg->status === 'failed')
                                            <i class="bi bi-exclamation-circle ms-1" title="Failed"></i>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                            <p class="mt-3">No messages yet. Start the conversation!</p>
                        </div>
                    @endforelse
                </div>

                <!-- Reply Input -->
                <div class="card-footer bg-white border-top" style="flex-shrink: 0;">
                    <form action="{{ route('inbox.reply', $conversation->id) }}" method="POST" id="replyForm">
                        @csrf
                        <div class="input-group">
                            <textarea class="form-control" name="message" id="messageInput" rows="2" placeholder="Type your message..." required style="resize: none;"></textarea>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send-fill"></i> Send
                            </button>
                        </div>
                        <small class="text-muted">Press Enter to send, Shift+Enter for new line</small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom on load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    // Restore draft from localStorage if available
    const messageInput = document.getElementById('messageInput');
    const draftKey = 'inbox_draft_{{ $conversation->id }}';
    const existingDraft = localStorage.getItem(draftKey);
    if (messageInput && existingDraft && !messageInput.value) {
        messageInput.value = existingDraft;
    }
});

// Enter key to send (Shift+Enter for new line)
document.getElementById('messageInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('replyForm').submit();
    }
});

// Persist draft to localStorage as user types
document.getElementById('messageInput')?.addEventListener('input', function() {
    const draftKey = 'inbox_draft_{{ $conversation->id }}';
    localStorage.setItem(draftKey, this.value);
});

// Clear draft on successful submit
document.getElementById('replyForm')?.addEventListener('submit', function() {
    const draftKey = 'inbox_draft_{{ $conversation->id }}';
    localStorage.removeItem(draftKey);
});

// Auto-refresh every 10 seconds for new messages
// Do not refresh while user is typing or has text in the input
(function() {
    const REFRESH_INTERVAL_MS = 10000;
    const messageInput = document.getElementById('messageInput');
    setInterval(function() {
        const isTyping = document.activeElement === messageInput;
        const hasDraft = (messageInput?.value || '').trim().length > 0;
        if (isTyping || hasDraft) {
            return; // Skip refresh to avoid losing user's input
        }
        location.reload();
    }, REFRESH_INTERVAL_MS);
})();
</script>

<style>
.message-bubble {
    word-wrap: break-word;
}

.message-bubble.bg-primary {
    border-bottom-right-radius: 4px !important;
}

.message-bubble.bg-white {
    border-bottom-left-radius: 4px !important;
    border: 1px solid #e9ecef;
}
</style>
@endsection

