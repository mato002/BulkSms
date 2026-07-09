@extends('layouts.app')

@section('page_title', $conversation->contact_name ?: 'Conversation')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <turbo-frame id="chat-panel"
                 data-panel-url="{{ route('inbox.panel', $conversation->id) }}"
                 data-refresh-interval="10000">
        @include('inbox.partials.chat-panel-content', compact('conversation', 'messages'))
    </turbo-frame>
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

<script data-turbo-eval>
function initInboxChat() {
    const messageInput = document.getElementById('messageInput');
    const replyForm = document.getElementById('replyForm');
    const conversationId = {{ $conversation->id }};
    const draftKey = `inbox_draft_${conversationId}`;

    if (messageInput) {
        const existingDraft = localStorage.getItem(draftKey);
        if (existingDraft && !messageInput.value) {
            messageInput.value = existingDraft;
        }

        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                replyForm?.requestSubmit();
            }
        });

        messageInput.addEventListener('input', function () {
            localStorage.setItem(draftKey, this.value);
        });
    }

    if (replyForm) {
        replyForm.addEventListener('submit', function () {
            localStorage.removeItem(draftKey);
        });
    }
}

document.addEventListener('bulksms:frame-load', function (event) {
    if (event.detail.frame.id === 'chat-panel') {
        initInboxChat();
    }
});

initInboxChat();
</script>
@endsection
