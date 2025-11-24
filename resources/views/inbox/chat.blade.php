@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <a href="{{ route('inbox.index') }}" class="btn-secondary-modern me-3">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back</span>
                </a>
                <div class="chat-avatar">
                    {{ strtoupper(substr($conversation->contact_name, 0, 1)) }}
                </div>
                <div class="chat-user-info">
                    <h5 class="chat-user-name">{{ $conversation->contact_name }}</h5>
                    <small class="chat-user-details">
                        {{ $conversation->contact_phone }}
                        @if($conversation->department)
                            • {{ $conversation->department }}
                        @endif
                    </small>
                </div>
            </div>
            <div class="chat-header-right">
                <span class="badge-modern badge-{{ $conversation->channel }}">
                    @if($conversation->channel === 'sms')
                        <i class="bi bi-phone"></i>
                    @elseif($conversation->channel === 'whatsapp')
                        <i class="bi bi-whatsapp"></i>
                    @else
                        <i class="bi bi-envelope"></i>
                    @endif
                    {{ strtoupper($conversation->channel) }}
                </span>
                <div class="dropdown">
                    <button class="btn-secondary-modern dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                        <span>{{ ucfirst($conversation->status) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-clock me-2"></i>Mark as Open
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-check-circle me-2"></i>Mark as Resolved
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="archived">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-archive me-2"></i>Archive
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="chat-messages" id="messagesContainer">
            @forelse($messages as $msg)
                <div class="message-wrapper {{ $msg->direction === 'outbound' ? 'message-outbound' : 'message-inbound' }}">
                    <div class="message-bubble {{ $msg->direction === 'outbound' ? 'bubble-outbound' : 'bubble-inbound' }}">
                        <div class="message-body">{{ $msg->body }}</div>
                        <div class="message-meta">
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
                <div class="chat-empty">
                    <i class="bi bi-chat-dots"></i>
                    <p>No messages yet. Start the conversation!</p>
                </div>
            @endforelse
        </div>

        <!-- Reply Input -->
        <div class="chat-footer">
            <form action="{{ route('inbox.reply', $conversation->id) }}" method="POST" id="replyForm">
                @csrf
                <div class="reply-input-wrapper">
                    <textarea class="reply-textarea" 
                              name="message" 
                              id="messageInput" 
                              rows="2" 
                              placeholder="Type your message..." 
                              required></textarea>
                    <button type="submit" class="btn-send">
                        <i class="bi bi-send-fill"></i>
                        <span>Send</span>
                    </button>
                </div>
                <small class="reply-hint">
                    <i class="bi bi-info-circle"></i>
                    Press Enter to send, Shift+Enter for new line
                </small>
            </form>
        </div>
    </div>
</div>

<style>
/* Chat Container */
.chat-container {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    max-height: 800px;
}

/* Chat Header */
.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.chat-header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.chat-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.chat-user-info {
    display: flex;
    flex-direction: column;
}

.chat-user-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.chat-user-details {
    color: #64748b;
    font-size: 0.8125rem;
}

/* Messages Area */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background: #f8fafc;
}

.message-wrapper {
    display: flex;
    margin-bottom: 1rem;
}

.message-inbound {
    justify-content: flex-start;
}

.message-outbound {
    justify-content: flex-end;
}

.message-bubble {
    max-width: 70%;
    padding: 0.875rem 1.125rem;
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    word-wrap: break-word;
}

.bubble-inbound {
    background: white;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
}

.bubble-outbound {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-body {
    font-size: 0.9375rem;
    line-height: 1.5;
    margin-bottom: 0.375rem;
}

.message-meta {
    font-size: 0.6875rem;
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.chat-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #94a3b8;
}

.chat-empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

/* Chat Footer */
.chat-footer {
    padding: 1.25rem;
    background: white;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.reply-input-wrapper {
    display: flex;
    gap: 0.75rem;
    align-items: flex-end;
}

.reply-textarea {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    resize: none;
    transition: all 0.2s;
}

.reply-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-send {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.reply-hint {
    color: #94a3b8;
    font-size: 0.75rem;
    margin-top: 0.5rem;
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-container {
        padding: 0.5rem;
    }
    
    .chat-container {
        height: calc(100vh - 100px);
        border-radius: 8px;
    }
    
    .chat-header {
        padding: 0.75rem;
        flex-wrap: wrap;
    }
    
    .chat-header-left {
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
    }
    
    .chat-header-right {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .chat-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    
    .chat-user-info {
        min-width: 0;
        flex: 1;
    }
    
    .chat-user-name {
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .chat-user-details {
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .chat-messages {
        padding: 0.75rem;
    }
    
    .message-bubble {
        max-width: 90%;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .message-meta {
        font-size: 0.65rem;
    }
    
    .chat-footer {
        padding: 0.75rem;
    }
    
    .reply-input-wrapper {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .reply-textarea {
        font-size: 0.9rem;
        padding: 0.75rem;
    }
    
    .btn-send {
        width: 100%;
        justify-content: center;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .reply-hint {
        font-size: 0.7rem;
    }
}

@media (max-width: 480px) {
    .chat-container {
        height: calc(100vh - 80px);
        border-radius: 0;
        margin: 0;
    }
    
    .chat-header {
        padding: 0.5rem;
    }
    
    .chat-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .chat-user-name {
        font-size: 0.85rem;
    }
    
    .chat-user-details {
        font-size: 0.7rem;
    }
    
    .chat-messages {
        padding: 0.5rem;
    }
    
    .message-bubble {
        max-width: 95%;
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
    }
    
    .chat-footer {
        padding: 0.5rem;
    }
    
    .reply-textarea {
        font-size: 0.85rem;
        padding: 0.6rem;
    }
    
    .btn-send {
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
    }
}
</style>

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
@endsection
