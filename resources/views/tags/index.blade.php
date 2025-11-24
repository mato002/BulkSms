@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-tags"></i> Contact Tags</h2>
            <p class="text-muted">Organize and segment your contacts with tags</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
            <i class="fas fa-plus"></i> Create Tag
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tags Grid -->
    <div class="row">
        @forelse($tags as $tag)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge" style="background-color: {{ $tag->color }}; font-size: 1rem; padding: 0.5rem 1rem;">
                                    <i class="fas fa-tag"></i> {{ $tag->name }}
                                </span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="editTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}', '{{ $tag->description }}')">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('tags.destroy', $tag->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="confirmDelete(event, 'Delete this tag? It will be removed from all contacts.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @if($tag->description)
                            <p class="text-muted small mb-3">{{ $tag->description }}</p>
                        @endif

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($tag->contacts_count) }}</h3>
                                <small class="text-muted">Contact{{ $tag->contacts_count != 1 ? 's' : '' }}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewTagContacts({{ $tag->id }})">
                                <i class="fas fa-users"></i> View Contacts
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                        <h4>No Tags Yet</h4>
                        <p class="text-muted">Create your first tag to start organizing contacts</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
                            <i class="fas fa-plus"></i> Create First Tag
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Create Tag Modal -->
<div class="modal fade" id="createTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tags.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tag Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., VIP, Premium, Late Payer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#3490dc">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this tag..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTagForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tag Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" id="edit_color" class="form-control form-control-color">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTag(id, name, color, description) {
    document.getElementById('editTagForm').action = '/tags/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_color').value = color;
    document.getElementById('edit_description').value = description || '';
    new bootstrap.Modal(document.getElementById('editTagModal')).show();
}

function viewTagContacts(tagId) {
    window.location.href = '/contacts?tag=' + tagId;
}
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>
@endsection


