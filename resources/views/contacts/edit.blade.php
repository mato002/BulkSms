@extends('layouts.app')

@section('content')
@include('components.modern-page-styles')

<div class="modern-page-container">
    <div class="modern-page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <div class="page-icon-wrapper">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Edit Contact</h1>
                    <p class="page-subtitle">Update contact information</p>
                </div>
            </div>
            <a href="{{ route('contacts.index') }}" class="btn-secondary-modern">
                <i class="bi bi-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title">
                <i class="bi bi-info-circle me-2"></i>Contact Information
            </h3>
        </div>
        <div class="modern-card-body">
            <form action="{{ route('contacts.update', $contact->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="name" class="modern-label">Name <span class="required">*</span></label>
                            <input type="text" class="modern-input @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $contact->name) }}" placeholder="Enter full name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="contact" class="modern-label">Phone Number <span class="required">*</span></label>
                            <div class="input-group">
                                <select class="modern-select" id="country_code" style="max-width: 120px;">
                                    <option value="+254">🇰🇪 +254</option>
                                    <option value="+255">🇹🇿 +255</option>
                                    <option value="+256">🇺🇬 +256</option>
                                    <option value="+250">🇷🇼 +250</option>
                                    <option value="+257">🇧🇮 +257</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                </select>
                                <input type="text" class="modern-input @error('contact') is-invalid @enderror" id="phone_number" placeholder="712345678" pattern="[0-9]{9,12}" value="{{ old('contact', $contact->contact) }}">
                                <input type="hidden" id="contact" name="contact" value="{{ old('contact', $contact->contact) }}" required>
                            </div>
                            <small class="form-hint">Enter phone number without leading zero</small>
                            @error('contact')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="modern-form-group">
                            <label for="department" class="modern-label">Department</label>
                            <input type="text" class="modern-input @error('department') is-invalid @enderror" id="department" name="department" value="{{ old('department', $contact->department) }}" placeholder="e.g., Sales, Marketing">
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('contacts.index') }}" class="btn-secondary-modern">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-check2-circle"></i>
                        <span>Update Contact</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modern-card" style="border-color: #fee2e2;">
        <div class="modern-card-header" style="background: #fef2f2; border-color: #fee2e2;">
            <h3 class="modern-card-title" style="color: #dc2626;">
                <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
            </h3>
        </div>
        <div class="modern-card-body">
            <p class="text-muted mb-3">Once you delete this contact, all associated messages will be lost. Please be certain.</p>
            <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this contact?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger-modern">
                    <i class="bi bi-trash"></i>
                    <span>Delete Contact</span>
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countryCode = document.getElementById('country_code');
    const phoneNumber = document.getElementById('phone_number');
    const contactHidden = document.getElementById('contact');
    
    function updateFullNumber() {
        let number = phoneNumber.value.trim().replace(/^0+/, '').replace(/\D/g, '');
        contactHidden.value = number ? countryCode.value + number : '';
    }
    
    const existingValue = phoneNumber.value;
    if (existingValue && existingValue.startsWith('+')) {
        for (let option of countryCode.options) {
            if (existingValue.startsWith(option.value)) {
                countryCode.value = option.value;
                phoneNumber.value = existingValue.substring(option.value.length);
                break;
            }
        }
    }
    
    countryCode.addEventListener('change', updateFullNumber);
    phoneNumber.addEventListener('input', updateFullNumber);
    updateFullNumber();
});
</script>
@endpush
