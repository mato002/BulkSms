@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h1 class="mb-0"><i class="bi bi-megaphone me-2"></i>Campaigns</h1>
            <span class="badge bg-light text-dark">Total: {{ number_format($campaigns->total()) }}</span>
        </div>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Create Campaign</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name or sender">
                </div>
                <div class="col-md-2">
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        <option value="sms" {{ request('channel')==='sms' ? 'selected' : '' }}>SMS</option>
                        <option value="whatsapp" {{ request('channel')==='whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status')==='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sending" {{ request('status')==='sending' ? 'selected' : '' }}>Sending</option>
                        <option value="sent" {{ request('status')==='sent' ? 'selected' : '' }}>Sent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Channel</th>
                            <th>Sender ID</th>
                            <th>Recipients</th>
                            <th>Status</th>
                            <th>Sent/Failed</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                        <tr>
                            <td class="fw-semibold">{{ $campaign->name }}</td>
                            <td>
                                @if(($campaign->channel ?? 'sms') === 'whatsapp')
                                    <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                                @else
                                    <span class="badge bg-primary"><i class="bi bi-chat-dots"></i> SMS</span>
                                @endif
                            </td>
                            <td>{{ $campaign->sender_id ?? '-' }}</td>
                            <td>{{ $campaign->total_recipients }}</td>
                            <td>
                                @if($campaign->status === 'sent')
                                    <span class="badge bg-success">Sent</span>
                                @elseif($campaign->status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($campaign->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $campaign->sent_count ?? 0 }} / {{ $campaign->failed_count ?? 0 }}</td>
                            <td>{{ \Carbon\Carbon::parse($campaign->created_at)->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                @if($campaign->status === 'draft')
                                    <form action="{{ route('campaigns.send', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Send this campaign now?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-send"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-megaphone" style="font-size: 2rem;"></i>
                                <div class="mt-2">No campaigns found. Create one to get started.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

