@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h1 class="mb-0"><i class="bi bi-people-fill me-2"></i>Contacts</h1>
            <span class="badge bg-light text-dark">Total: {{ number_format($contacts->total()) }}</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
            </button>
            <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Contact
            </a>
        </div>
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
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name or phone">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="department" value="{{ request('department') }}" placeholder="Filter by department">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ route('contacts.index') }}">Clear</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:56px"></th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Department</th>
                            <th>Last Activity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                        <tr>
                            <td>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; font-weight: 600;">
                                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('inbox.start', $contact->id) }}" class="fw-semibold text-decoration-none text-dark hover-primary">
                                    {{ $contact->name }}
                                </a>
                            </td>
                            <td>{{ $contact->contact }}</td>
                            <td>
                                @if($contact->department)
                                    <span class="badge bg-light text-dark">{{ $contact->department }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($contact->last_message_at ?? $contact->created_at)->diffForHumans() }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('inbox.start', $contact->id) }}" class="btn btn-sm btn-outline-success" title="Send Message">
                                        <i class="bi bi-chat-dots"></i>
                                    </a>
                                    <a href="{{ route('contacts.edit', $contact->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Contact">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Delete this contact?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Contact">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-people" style="font-size: 2rem;"></i>
                                <div class="mt-2">No contacts found. Add one or import CSV.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('contacts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Contacts from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV File</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        <div class="form-text">
                            <strong>Expected format:</strong> Name, Phone Number, Department (optional)<br>
                            <small><i class="bi bi-info-circle"></i> Phone numbers can be with or without country code. If no country code is provided, +254 (Kenya) will be added automatically.</small><br>
                            <small><strong>Example:</strong> John Doe, 0712345678, Sales</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hover-primary:hover {
        color: #0d6efd !important;
        cursor: pointer;
    }
</style>
@endpush

