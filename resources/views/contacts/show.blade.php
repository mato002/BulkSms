@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h1 class="page-main-title">{{ $contact->name }}</h1>
                    <p class="page-subtitle">Contact Details</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('contacts.edit', $contact->id) }}" class="btn-warning-modern">
                    <i class="bi bi-pencil"></i>
                    <span>Edit</span>
                </a>
                <a href="{{ route('contacts.index') }}" class="btn-secondary-modern">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Contact Information Card -->
        <div class="col-lg-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h3 class="modern-card-title">
                        <i class="bi bi-info-circle me-2"></i>Contact Information
                    </h3>
                </div>
                <div class="modern-card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="120">Contact ID:</td>
                            <td class="fw-bold font-monospace">{{ $contact->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Name:</td>
                            <td class="fw-semibold">{{ $contact->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td>
                                <a href="tel:{{ $contact->contact }}" class="text-decoration-none">
                                    {{ $contact->contact }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Department:</td>
                            <td>
                                @if($contact->department)
                                    <span class="badge bg-info">{{ $contact->department }}</span>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Opted In:</td>
                            <td>
                                @if($contact->opted_in ?? true)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Yes
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>No
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ \Carbon\Carbon::parse($contact->created_at)->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ \Carbon\Carbon::parse($contact->updated_at)->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Tags Card -->
            @if($contactTags->count() > 0)
                <div class="modern-card mt-4">
                    <div class="modern-card-header">
                        <h3 class="modern-card-title">
                            <i class="bi bi-tags me-2"></i>Tags
                        </h3>
                    </div>
                    <div class="modern-card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($contactTags as $tag)
                                <span class="badge" style="background-color: {{ $tag->color ?? '#6c757d' }};">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Statistics & Activity Card -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="modern-card">
                        <div class="modern-card-body text-center">
                            <div class="mb-2">
                                <i class="bi bi-chat-dots text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ number_format($contact->total_messages ?? 0) }}</h4>
                            <p class="text-muted mb-0 small">Total Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="modern-card">
                        <div class="modern-card-body text-center">
                            <div class="mb-2">
                                <i class="bi bi-envelope-exclamation text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ number_format($contact->unread_messages ?? 0) }}</h4>
                            <p class="text-muted mb-0 small">Unread Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="modern-card">
                        <div class="modern-card-body text-center">
                            <div class="mb-2">
                                <i class="bi bi-clock-history text-info" style="font-size: 2rem;"></i>
                            </div>
                            <h6 class="mb-1">
                                @if($contact->last_message_at)
                                    {{ \Carbon\Carbon::parse($contact->last_message_at)->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </h6>
                            <p class="text-muted mb-0 small">Last Message</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h3 class="modern-card-title">
                        <i class="bi bi-clock-history me-2"></i>Recent Messages
                    </h3>
                </div>
                <div class="modern-card-body p-0">
                    @if($recentMessages->count() > 0)
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Direction</th>
                                        <th>Message</th>
                                        <th>Channel</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMessages as $message)
                                        <tr>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($message->created_at)->format('M d, Y H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($message->sender === $contact->contact)
                                                    <span class="badge bg-info">Inbound</span>
                                                @else
                                                    <span class="badge bg-primary">Outbound</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $message->body }}">
                                                    {{ Str::limit($message->body, 50) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($message->channel ?? 'sms') }}</span>
                                            </td>
                                            <td>
                                                @if($message->status === 'delivered')
                                                    <span class="badge bg-success">Delivered</span>
                                                @elseif($message->status === 'failed')
                                                    <span class="badge bg-danger">Failed</span>
                                                @elseif($message->status === 'sent')
                                                    <span class="badge bg-primary">Sent</span>
                                                @else
                                                    <span class="badge bg-warning">{{ ucfirst($message->status ?? 'Pending') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">No messages found</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($contact->notes ?? null)
                <div class="modern-card mt-4">
                    <div class="modern-card-header">
                        <h3 class="modern-card-title">
                            <i class="bi bi-sticky me-2"></i>Notes
                        </h3>
                    </div>
                    <div class="modern-card-body">
                        <p class="mb-0">{{ $contact->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Custom Fields -->
            @php
                $customFields = is_string($contact->custom_fields) ? json_decode($contact->custom_fields, true) : ($contact->custom_fields ?? []);
            @endphp
            @if(!empty($customFields))
                <div class="modern-card mt-4">
                    <div class="modern-card-header">
                        <h3 class="modern-card-title">
                            <i class="bi bi-list-ul me-2"></i>Custom Fields
                        </h3>
                    </div>
                    <div class="modern-card-body">
                        <table class="table table-sm table-borderless mb-0">
                            @foreach($customFields as $key => $value)
                                <tr>
                                    <td class="text-muted" width="150">{{ ucfirst(str_replace('_', ' ', $key)) }}:</td>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

