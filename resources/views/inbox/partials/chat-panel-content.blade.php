<div class="chat-container">
    <div class="chat-header">
        <div class="chat-header-left">
            <a href="{{ route('inbox.index') }}" class="btn-secondary-modern me-3" data-turbo-frame="main-content" data-turbo-action="advance">
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
                        <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST" data-turbo-frame="chat-panel">
                            @csrf
                            <input type="hidden" name="status" value="open">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-clock me-2"></i>Mark as Open
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST" data-turbo-frame="chat-panel">
                            @csrf
                            <input type="hidden" name="status" value="resolved">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-check-circle me-2"></i>Mark as Resolved
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('inbox.updateStatus', $conversation->id) }}" method="POST" data-turbo-frame="chat-panel">
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
                                <i class="bi bi-exclamation-circle ms-1" title="Failed: {{ $msg->error_message ?? 'Unknown error' }}"></i>
                            @elseif(in_array($msg->status, ['sending', 'queued']))
                                <i class="bi bi-clock ms-1" title="Sending"></i>
                            @else
                                <i class="bi bi-question-circle ms-1" title="{{ ucfirst($msg->status) }}"></i>
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

    <div class="chat-footer">
        <form action="{{ route('inbox.reply', $conversation->id) }}" method="POST" id="replyForm" data-turbo-frame="chat-panel">
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
