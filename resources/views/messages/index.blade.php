@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Conversations</h1>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('messages.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search name, phone, or message..." value="{{ request('search') }}">
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
                    <a href="{{ route('messages.index') }}" class="btn btn-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Contact</th>
                            <th>Phone/Identifier</th>
                            <th>Channel</th>
                            <th>Last Message</th>
                            <th>Direction</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th>Unread</th>
                            <th>Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conversations as $c)
                        <tr>
                            <td>{{ $c->contact_name ?? 'Unknown' }}</td>
                            <td>{{ $c->contact_phone }}</td>
                            <td><span class="badge bg-secondary">{{ strtoupper($c->channel) }}</span></td>
                            <td class="text-truncate" style="max-width: 360px;">{{ $c->last_message_preview }}</td>
                            <td>
                                @if($c->last_message_direction === 'inbound')
                                    <span class="badge bg-success">Inbound</span>
                                @else
                                    <span class="badge bg-primary">Outbound</span>
                                @endif
                            </td>
                            <td>
                                @if($c->status === 'resolved')
                                    <span class="badge bg-success">Resolved</span>
                                @elseif($c->status === 'archived')
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-warning text-dark">Open</span>
                                @endif
                            </td>
                            <td>{{ $c->last_message_at ? \Carbon\Carbon::parse($c->last_message_at)->diffForHumans() : '-' }}</td>
                            <td>
                                @if(($c->unread_count ?? 0) > 0)
                                    <span class="badge bg-danger">{{ $c->unread_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inbox.show', $c->id) }}" class="btn btn-sm btn-outline-primary">Open Chat</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No conversations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $conversations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

