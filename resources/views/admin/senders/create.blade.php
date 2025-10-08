@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-plus-circle me-2"></i>Add New Sender</h1>
            <p class="text-muted mb-0">Create a new sender/tenant with API credentials</p>
        </div>
        <a href="{{ route('admin.senders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Senders
        </a>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.senders.store') }}">
                        @csrf

                        <!-- Sender Information Section -->
                        <h5 class="mb-3">Sender Information</h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Sender Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g., Acme Corporation">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" 
                                   name="company_name" 
                                   id="company_name" 
                                   value="{{ old('company_name') }}"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   placeholder="e.g., Acme Technologies Ltd">
                            <div class="form-text">Optional - If not provided, Sender ID will be used</div>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sender_id" class="form-label">Sender ID <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="sender_id" 
                                       id="sender_id" 
                                       value="{{ old('sender_id') }}"
                                       required
                                       maxlength="11"
                                       class="form-control @error('sender_id') is-invalid @enderror"
                                       placeholder="e.g., ACME_TECH">
                                <div class="form-text">Max 11 characters, will be converted to uppercase</div>
                                @error('sender_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact" class="form-label">Contact (Email/Phone) <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="contact" 
                                       id="contact" 
                                       value="{{ old('contact') }}"
                                       required
                                       class="form-control @error('contact') is-invalid @enderror"
                                       placeholder="contact@example.com">
                                @error('contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="balance" class="form-label">Initial Balance (KSH)</label>
                                <input type="number" 
                                       name="balance" 
                                       id="balance" 
                                       value="{{ old('balance', 0) }}"
                                       step="0.01"
                                       min="0"
                                       class="form-control @error('balance') is-invalid @enderror"
                                       placeholder="0.00">
                                @error('balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="price_per_unit" class="form-label">Price Per Unit (KSH)</label>
                                <input type="number" 
                                       name="price_per_unit" 
                                       id="price_per_unit" 
                                       value="{{ old('price_per_unit', 1.00) }}"
                                       step="0.01"
                                       min="0.01"
                                       class="form-control @error('price_per_unit') is-invalid @enderror"
                                       placeholder="1.00">
                                <div class="form-text">Cost of 1 SMS/WhatsApp unit</div>
                                @error('price_per_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Onfon Wallet Configuration -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Onfon Wallet Integration (Optional)</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="enable_onfon" 
                                       {{ old('enable_onfon') ? 'checked' : '' }}
                                       onchange="toggleOnfonFields()">
                                <label class="form-check-label" for="enable_onfon">
                                    Enable Onfon Wallet
                                </label>
                            </div>
                        </div>

                        <div id="onfon-fields" class="{{ old('enable_onfon') ? '' : 'd-none' }}">
                            <div class="alert alert-info d-flex align-items-start mb-3">
                                <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                <div class="small">
                                    Configure Onfon Media wallet to enable automatic balance sync and SMS sending through Onfon gateway.
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="onfon_api_key" class="form-label">Onfon API Key</label>
                                    <input type="text" 
                                           name="onfon_api_key" 
                                           id="onfon_api_key" 
                                           value="{{ old('onfon_api_key') }}"
                                           class="form-control @error('onfon_api_key') is-invalid @enderror"
                                           placeholder="Your Onfon API Key">
                                    @error('onfon_api_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="onfon_client_id" class="form-label">Onfon Client ID</label>
                                    <input type="text" 
                                           name="onfon_client_id" 
                                           id="onfon_client_id" 
                                           value="{{ old('onfon_client_id') }}"
                                           class="form-control @error('onfon_client_id') is-invalid @enderror"
                                           placeholder="Your Onfon Client ID">
                                    @error('onfon_client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="onfon_access_key" class="form-label">Access Key Header (Optional)</label>
                                    <input type="text" 
                                           name="onfon_access_key" 
                                           id="onfon_access_key" 
                                           value="{{ old('onfon_access_key', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB') }}"
                                           class="form-control"
                                           placeholder="Access Key">
                                    <div class="form-text">Default value will be used if empty</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="default_sender" class="form-label">Default Onfon Sender</label>
                                    <input type="text" 
                                           name="default_sender" 
                                           id="default_sender" 
                                           value="{{ old('default_sender') }}"
                                           maxlength="11"
                                           class="form-control"
                                           placeholder="e.g., ACME_TECH">
                                    <div class="form-text">Will use Sender ID if empty</div>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="auto_sync_balance" 
                                       id="auto_sync_balance" 
                                       value="1"
                                       {{ old('auto_sync_balance') ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_sync_balance">
                                    Auto-sync balance from Onfon wallet periodically
                                </label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- User Account Section (Optional) -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Create User Account (Optional)</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="create_user" 
                                       id="create_user" 
                                       value="1"
                                       {{ old('create_user') ? 'checked' : '' }}
                                       onchange="toggleUserFields()">
                                <label class="form-check-label" for="create_user">
                                    Create a user account for this sender
                                </label>
                            </div>
                        </div>

                        <div id="user-fields" class="{{ old('create_user') ? '' : 'd-none' }}">
                            <div class="mb-3">
                                <label for="user_name" class="form-label">User Name</label>
                                <input type="text" 
                                       name="user_name" 
                                       id="user_name" 
                                       value="{{ old('user_name') }}"
                                       class="form-control @error('user_name') is-invalid @enderror"
                                       placeholder="John Doe">
                                @error('user_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="user_email" class="form-label">User Email</label>
                                <input type="email" 
                                       name="user_email" 
                                       id="user_email" 
                                       value="{{ old('user_email') }}"
                                       class="form-control @error('user_email') is-invalid @enderror"
                                       placeholder="user@example.com">
                                @error('user_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="user_password" class="form-label">User Password</label>
                                <input type="password" 
                                       name="user_password" 
                                       id="user_password" 
                                       class="form-control @error('user_password') is-invalid @enderror"
                                       placeholder="Minimum 8 characters">
                                <div class="form-text">Minimum 8 characters</div>
                                @error('user_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                            <div>
                                <strong>Note:</strong> An API key will be automatically generated for this sender. You'll see it after creation. Make sure to save it securely.
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.senders.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Create Sender
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightbulb me-2"></i>Quick Tips</h5>
                    <ul class="small mb-0">
                        <li class="mb-2">The <strong>Sender ID</strong> should be unique and memorable</li>
                        <li class="mb-2">Maximum 11 characters for Sender ID</li>
                        <li class="mb-2">The API key will be shown only once after creation</li>
                        <li class="mb-2">You can create a user account to give this sender login access</li>
                        <li>Balance can be updated later from the sender details page</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleUserFields() {
    const checkbox = document.getElementById('create_user');
    const userFields = document.getElementById('user-fields');
    
    if (checkbox.checked) {
        userFields.classList.remove('d-none');
    } else {
        userFields.classList.add('d-none');
    }
}

function toggleOnfonFields() {
    const checkbox = document.getElementById('enable_onfon');
    const onfonFields = document.getElementById('onfon-fields');
    
    if (checkbox.checked) {
        onfonFields.classList.remove('d-none');
    } else {
        onfonFields.classList.add('d-none');
    }
}

// Calculate units from balance and price
document.getElementById('balance')?.addEventListener('input', calculateUnits);
document.getElementById('price_per_unit')?.addEventListener('input', calculateUnits);

function calculateUnits() {
    const balance = parseFloat(document.getElementById('balance').value) || 0;
    const pricePerUnit = parseFloat(document.getElementById('price_per_unit').value) || 1;
    const units = pricePerUnit > 0 ? (balance / pricePerUnit).toFixed(2) : 0;
    
    // Show units preview near balance field
    let preview = document.getElementById('units-preview');
    if (!preview) {
        preview = document.createElement('small');
        preview.id = 'units-preview';
        preview.className = 'text-muted d-block mt-1';
        document.getElementById('balance').parentElement.appendChild(preview);
    }
    preview.textContent = `≈ ${units} units`;
}
</script>
@endpush
@endsection
