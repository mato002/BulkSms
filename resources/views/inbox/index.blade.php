@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-inbox-fill me-2"></i>Inbox</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('inbox.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search conversations..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="channel">
                        <option value="">All Channels</option>
                        <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                        <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('inbox.index') }}" class="btn btn-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversations List -->
    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($conversations as $conv)
                <a href="{{ route('inbox.show', $conv->id) }}" class="list-group-item list-group-item-action {{ $conv->unread_count > 0 ? 'bg-light' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-weight: bold;">
                                        {{ strtoupper(substr($conv->contact_name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 {{ $conv->unread_count > 0 ? 'fw-bold' : '' }}">
                                        {{ $conv->contact_name }}
                                        @if($conv->unread_count > 0)
                                            <span class="badge bg-danger ms-2">{{ $conv->unread_count }}</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $conv->contact_phone }}</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        {{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans() : 'Never' }}
                                    </small>
                                    <br>
                                    <span class="badge bg-secondary">{{ strtoupper($conv->channel) }}</span>
                                    @if($conv->status === 'resolved')
                                        <span class="badge bg-success">Resolved</span>
                                    @elseif($conv->status === 'archived')
                                        <span class="badge bg-secondary">Archived</span>
                                    @endif
                                </div>
                            </div>
                            <p class="mb-1 text-truncate {{ $conv->unread_count > 0 ? 'fw-semibold' : 'text-muted' }}">
                                @if($conv->last_message_direction === 'inbound')
                                    <i class="bi bi-arrow-down-circle text-success me-1"></i>
                                @else
                                    <i class="bi bi-arrow-up-circle text-primary me-1"></i>
                                @endif
                                {{ $conv->last_message_preview }}
                            </p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 4rem;"></i>
                    <p class="mt-3">No conversations yet</p>
                    <p class="small">Conversations will appear here when customers reply to your messages</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($conversations->hasPages())
    <div class="mt-3">
        {{ $conversations->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

