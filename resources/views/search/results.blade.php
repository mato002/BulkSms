@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Search Results</h1>
            <p class="text-muted">Showing results for: <strong>"{{ $query }}"</strong></p>
        </div>
    </div>

    @if($contacts->isEmpty() && $messages->isEmpty() && $campaigns->isEmpty() && $templates->isEmpty() && $conversations->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search display-1 text-muted"></i>
                        <h3 class="mt-3">No Results Found</h3>
                        <p class="text-muted">We couldn't find any results for "{{ $query }}". Try using different keywords.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Contacts Results -->
        @if($contacts->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Contacts ({{ $contacts->total() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Department</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contacts as $contact)
                                    <tr>
                                        <td>{{ $contact->name }}</td>
                                        <td>{{ $contact->contact }}</td>
                                        <td>{{ $contact->department ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('contacts.show', $contact->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($contacts->hasPages())
                    <div class="card-footer bg-white">
                        {{ $contacts->appends(['q' => $query])->links('vendor.pagination.simple') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Messages Results -->
        @if($messages->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Messages ({{ $messages->total() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>To</th>
                                        <th>Channel</th>
                                        <th>Content</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                    <tr>
                                        <td>{{ $message->to }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($message->channel) }}</span></td>
                                        <td>{{ Str::limit($message->content, 50) }}</td>
                                        <td>{{ $message->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('messages.show', $message->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($messages->hasPages())
                    <div class="card-footer bg-white">
                        {{ $messages->appends(['q' => $query])->links('vendor.pagination.simple') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Campaigns Results -->
        @if($campaigns->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Campaigns ({{ $campaigns->total() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign->name }}</td>
                                        <td>
                                            @if($campaign->status === 'completed')
                                                <span class="badge bg-success">{{ ucfirst($campaign->status) }}</span>
                                            @elseif($campaign->status === 'pending')
                                                <span class="badge bg-warning">{{ ucfirst($campaign->status) }}</span>
                                            @elseif($campaign->status === 'active')
                                                <span class="badge bg-primary">{{ ucfirst($campaign->status) }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($campaign->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($campaign->message, 50) }}</td>
                                        <td>
                                            <a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($campaigns->hasPages())
                    <div class="card-footer bg-white">
                        {{ $campaigns->appends(['q' => $query])->links('vendor.pagination.simple') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Templates Results -->
        @if($templates->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Templates ({{ $templates->total() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Channel</th>
                                        <th>Content</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($template->channel) }}</span></td>
                                        <td>{{ Str::limit($template->content, 50) }}</td>
                                        <td>
                                            <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($templates->hasPages())
                    <div class="card-footer bg-white">
                        {{ $templates->appends(['q' => $query])->links('vendor.pagination.simple') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Conversations Results -->
        @if($conversations->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Conversations ({{ $conversations->total() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Contact Name</th>
                                        <th>Phone</th>
                                        <th>Last Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conversations as $conversation)
                                    <tr>
                                        <td>{{ $conversation->contact_name }}</td>
                                        <td>{{ $conversation->contact_phone }}</td>
                                        <td>{{ Str::limit($conversation->last_message_preview, 50) }}</td>
                                        <td>
                                            <a href="{{ route('inbox.show', $conversation->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($conversations->hasPages())
                    <div class="card-footer bg-white">
                        {{ $conversations->appends(['q' => $query])->links('vendor.pagination.simple') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
@endsection


