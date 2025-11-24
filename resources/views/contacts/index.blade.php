@extends('layouts.app')

@section('content')
<div class="modern-page-container">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Contacts</h1>
                    <p class="page-subtitle">Manage your contact list and send messages</p>
                </div>
            </div>
            <div class="header-buttons">
                <button type="button" class="btn-secondary-modern" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    <span>Import CSV</span>
                </button>
                <a href="{{ route('contacts.create') }}" class="btn-primary-modern">
                    <i class="bi bi-plus-lg"></i>
                    <span>Add Contact</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="bulkActionForm" action="{{ route('contacts.bulk-action') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_action" id="bulkActionInput">
        <input type="hidden" name="tag_id" id="bulkTagInput">
    </form>

    <div id="bulkActionToolbar" class="bulk-action-toolbar shadow-sm" style="display: none;">
        <div class="toolbar-main">
            <div class="toolbar-count">
                <span id="selectedCount">0</span> selected
            </div>
            <div class="toolbar-actions">
                <div class="toolbar-group">
                    <label for="bulkTagSelect" class="visually-hidden">Select tag</label>
                    <select id="bulkTagSelect" class="form-select form-select-sm" {{ $availableTags->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Apply tag...</option>
                        @foreach($availableTags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    @if($availableTags->isEmpty())
                        <a href="{{ route('tags.index') }}" class="small text-decoration-none ms-2">
                            Create tags
                        </a>
                    @endif
                    <button type="button" class="btn btn-sm btn-primary ms-2" id="applyTagBtn" disabled>
                        <i class="bi bi-tags me-1"></i>Apply Tag
                    </button>
                </div>
                <div class="toolbar-group">
                    <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelectionBtn">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="bulkActionOverlay" class="bulk-action-overlay" style="display: none;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Processing...</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-primary-gradient">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Total Contacts</div>
                <div class="stat-value-modern">{{ number_format($contacts->total()) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-success-gradient">
                <i class="bi bi-chat-dots"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Active Chats</div>
                <div class="stat-value-modern">{{ number_format($contacts->whereNotNull('last_message_at')->count()) }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-info-gradient">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Departments</div>
                <div class="stat-value-modern">{{ $contacts->whereNotNull('department')->pluck('department')->unique()->count() }}</div>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-icon-modern bg-warning-gradient">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content-modern">
                <div class="stat-label-modern">Recent Activity</div>
                <div class="stat-value-modern">{{ $contacts->where('last_message_at', '>=', now()->subDays(7))->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-funnel me-2"></i>Filter & Search
            </h3>
        </div>
        <div class="modern-card-body">
            <form method="GET" class="modern-filter-form">
                <div class="filter-group">
                    <input type="text" name="search" class="modern-input" value="{{ request('search') }}" placeholder="Search by name or phone...">
                </div>
                <div class="filter-group">
                    <input type="text" name="department" class="modern-input" value="{{ request('department') }}" placeholder="Filter by department...">
                </div>
                <div class="filter-actions">
                    <button class="btn-primary-modern" type="submit">
                        <i class="bi bi-search"></i>
                        <span>Search</span>
                    </button>
                    <a href="{{ route('contacts.index') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-list-ul me-2"></i>All Contacts
            </h3>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">
                                <div class="form-check">
                                    <input class="form-check-input contact-select-all" type="checkbox" id="selectAllContacts">
                                </div>
                            </th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Department</th>
                            <th>Last Activity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input contact-checkbox" type="checkbox" data-contact-id="{{ $contact->id }}">
                                    </div>
                                    <div class="contact-avatar">
                                        {{ strtoupper(substr($contact->name, 0, 1)) }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('contacts.show', $contact->id) }}" class="contact-name">
                                    {{ $contact->name }}
                                </a>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <i class="bi bi-phone"></i>
                                    <span>{{ $contact->contact }}</span>
                                </div>
                            </td>
                            <td>
                                @if($contact->department)
                                    <span class="badge-department">{{ $contact->department }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($contact->last_message_at ?? $contact->created_at)->diffForHumans() }}</td>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="{{ route('contacts.show', $contact->id) }}" class="btn-action btn-action-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('inbox.start', $contact->id) }}" class="btn-action btn-action-success" title="Send Message">
                                        <i class="bi bi-chat-dots"></i>
                                    </a>
                                    <a href="{{ route('contacts.edit', $contact->id) }}" class="btn-action btn-action-primary" title="Edit Contact">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-danger" title="Delete Contact" onclick="confirmDelete(event, 'Are you sure you want to delete this contact? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <h4>No Contacts Found</h4>
                                    <p>Add your first contact or import from CSV</p>
                                    <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                                        <a href="{{ route('contacts.create') }}" class="btn-primary-small">
                                            <i class="bi bi-plus-circle"></i>
                                            Add Contact
                                        </a>
                                        <button type="button" class="btn-secondary-modern" data-bs-toggle="modal" data-bs-target="#importModal">
                                            <i class="bi bi-file-earmark-arrow-up"></i>
                                            Import CSV
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($contacts->hasPages())
        <div class="modern-card-footer">
            {{ $contacts->appends(request()->query())->links('vendor.pagination.simple') }}
        </div>
        @endif
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

<style>
/* Modern Page Container */
.modern-page-container {
    padding: 1.5rem;
    max-width: 100%;
}

.bulk-action-toolbar {
    position: fixed;
    left: 50%;
    bottom: 1.5rem;
    transform: translateX(-50%);
    background: #0f172a;
    color: #f8fafc;
    border-radius: 999px;
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    z-index: 1040;
    min-width: 280px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.35);
}

.bulk-action-toolbar .toolbar-main {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.bulk-action-toolbar .toolbar-count {
    font-weight: 600;
}

.bulk-action-toolbar .toolbar-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.bulk-action-toolbar .toolbar-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bulk-action-toolbar select.form-select-sm {
    min-width: 160px;
    border-radius: 999px;
    border: none;
    padding-left: 1rem;
    padding-right: 2rem;
}

.bulk-action-toolbar .btn {
    border-radius: 999px;
    padding: 0.35rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.bulk-action-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.modern-table tr.selected-row {
    background: rgba(59, 130, 246, 0.08);
}

.modern-table tr.selected-row:hover {
    background: rgba(59, 130, 246, 0.12);
}

/* Page Header */
.modern-page-header {
    margin-bottom: 1.5rem;
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.page-title-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.page-main-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.page-subtitle {
    color: #64748b;
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
}

.header-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card-modern {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card-modern:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.stat-icon-modern {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.bg-primary-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-content-modern {
    flex: 1;
}

.stat-label-modern {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.stat-value-modern {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

/* Modern Card */
.modern-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.modern-card-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.modern-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
}

.modern-card-body {
    padding: 1.25rem;
}

.modern-card-footer {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

/* Filter Form */
.modern-filter-form {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.modern-input {
    padding: 0.625rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s;
    width: 100%;
}

.modern-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Buttons */
.btn-primary-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    text-decoration: none;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-secondary-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary-modern:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
}

.btn-primary-small {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary-small:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
}

/* Modern Table */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.modern-table tbody tr {
    transition: background 0.2s;
}

.modern-table tbody tr:hover {
    background: #f8fafc;
}

.modern-table tbody td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.contact-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.contact-name {
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
    transition: color 0.2s;
}

.contact-name:hover {
    color: #667eea;
}

.contact-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
}

.contact-info i {
    color: #94a3b8;
}

.badge-department {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action-success:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-action-primary:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.btn-action-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-filter-form {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        width: 100%;
    }
    
    .filter-actions .btn-primary-modern,
    .filter-actions .btn-secondary-modern {
        flex: 1;
    }
}

@media (max-width: 768px) {
    .modern-page-container {
        padding: 1rem;
    }
    
    .bulk-action-toolbar {
        bottom: 1rem;
        width: calc(100% - 2rem);
        border-radius: 1rem;
        padding: 1rem;
        transform: translateX(-50%);
    }

    .bulk-action-toolbar .toolbar-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .bulk-action-toolbar .toolbar-actions {
        width: 100%;
        flex-direction: column;
        gap: 0.75rem;
    }

    .bulk-action-toolbar .toolbar-group {
        width: 100%;
    }

    .bulk-action-toolbar select.form-select-sm {
        width: 100%;
    }

    .bulk-action-toolbar .btn {
        width: 100%;
        justify-content: center;
    }

    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-buttons {
        width: 100%;
    }
    
    .header-buttons .btn-primary-modern,
    .header-buttons .btn-secondary-modern {
        flex: 1;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllContacts');
    const checkboxNodes = document.querySelectorAll('.contact-checkbox');
    const toolbar = document.getElementById('bulkActionToolbar');
    const overlay = document.getElementById('bulkActionOverlay');
    const bulkForm = document.getElementById('bulkActionForm');
    const bulkActionInput = document.getElementById('bulkActionInput');
    const bulkTagInput = document.getElementById('bulkTagInput');
    const tagSelect = document.getElementById('bulkTagSelect');
    const applyTagBtn = document.getElementById('applyTagBtn');
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const clearBtn = document.getElementById('clearSelectionBtn');
    const selectedCountEl = document.getElementById('selectedCount');

    if (!bulkForm) {
        return;
    }

    const selectedIds = new Set();

    function updateToolbarVisibility() {
        if (!toolbar) {
            return;
        }

        const selectedCount = selectedIds.size;
        selectedCountEl.textContent = selectedCount;

        if (selectedCount > 0) {
            toolbar.style.display = 'block';
            document.body.classList.add('bulk-toolbar-visible');
        } else {
            toolbar.style.display = 'none';
            document.body.classList.remove('bulk-toolbar-visible');
        }

        checkboxNodes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (row) {
                row.classList.toggle('selected-row', selectedIds.has(checkbox.dataset.contactId));
            }
        });

        if (selectAll) {
            if (selectedCount === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (selectedCount === checkboxNodes.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.indeterminate = true;
                selectAll.checked = false;
            }
        }

        if (applyTagBtn && tagSelect) {
            applyTagBtn.disabled = !tagSelect.value || selectedCount === 0;
        }
    }

    function toggleSelection(checkbox) {
        const contactId = checkbox.dataset.contactId;
        if (!contactId) {
            return;
        }

        if (checkbox.checked) {
            selectedIds.add(contactId);
        } else {
            selectedIds.delete(contactId);
        }

        updateToolbarVisibility();
    }

    checkboxNodes.forEach(checkbox => {
        checkbox.addEventListener('change', () => toggleSelection(checkbox));
    });

    selectAll?.addEventListener('change', function () {
        const shouldSelect = this.checked;
        checkboxNodes.forEach(cb => {
            cb.checked = shouldSelect;
            const contactId = cb.dataset.contactId;
            if (!contactId) {
                return;
            }
            if (shouldSelect) {
                selectedIds.add(contactId);
            } else {
                selectedIds.delete(contactId);
            }
        });
        updateToolbarVisibility();
    });

    tagSelect?.addEventListener('change', () => {
        if (applyTagBtn) {
            applyTagBtn.disabled = !tagSelect.value || selectedIds.size === 0;
        }
    });

    clearBtn?.addEventListener('click', function () {
        checkboxNodes.forEach(cb => {
            cb.checked = false;
        });
        selectedIds.clear();
        updateToolbarVisibility();
    });

    function submitBulkAction(action) {
        if (selectedIds.size === 0) {
            window.alert('Select at least one contact to continue.');
            return;
        }

        if (action === 'tag') {
            if (!tagSelect || !tagSelect.value) {
                window.alert('Choose a tag before applying it to contacts.');
                return;
            }
            bulkTagInput.value = tagSelect.value;
        }

        if (action === 'delete') {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete the selected contacts? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-action-form').submit();
                }
            });
            return false;
        }
        if (false) {
            return;
        }

        bulkActionInput.value = action;

        // Remove previous dynamic inputs
        bulkForm.querySelectorAll('input[name="selected_contacts[]"]').forEach(el => el.remove());

        selectedIds.forEach(id => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_contacts[]';
            hiddenInput.value = id;
            bulkForm.appendChild(hiddenInput);
        });

        if (overlay) {
            overlay.style.display = 'flex';
        }

        bulkForm.submit();
    }

    applyTagBtn?.addEventListener('click', function () {
        submitBulkAction('tag');
    });

    deleteBtn?.addEventListener('click', function () {
        submitBulkAction('delete');
    });
});
</script>
@endpush

@endsection
