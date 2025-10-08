@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h1 class="mb-0"><i class="bi bi-file-text me-2"></i>Templates</h1>
            <span class="badge bg-light text-dark">Total: {{ number_format($templates->total()) }}</span>
        </div>
        <a href="{{ route('templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Create Template</a>
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
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name or text">
                </div>
                <div class="col-md-3">
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        <option value="sms" {{ request('channel')==='sms' ? 'selected' : '' }}>SMS</option>
                        <option value="whatsapp" {{ request('channel')==='whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="email" {{ request('channel')==='email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Channel</th>
                            <th>Subject</th>
                            <th>Preview</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr>
                            <td class="fw-semibold">{{ $template->name }}</td>
                            <td><span class="badge bg-secondary">{{ strtoupper($template->channel) }}</span></td>
                            <td>{{ $template->subject ?? '-' }}</td>
                            <td>{{ \Str::limit($template->body, 50) }}</td>
                            <td>{{ \Carbon\Carbon::parse($template->created_at)->format('M d, Y') }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                                <div class="mt-2">No templates found. Create one to get started.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

