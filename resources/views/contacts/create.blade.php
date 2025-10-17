@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <h1 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Add Contact</h1>
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary w-100 w-sm-auto"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('contacts.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="contact" class="form-label">Phone Number *</label>
                        <div class="input-group">
                            <select class="form-select" id="country_code" style="max-width: 120px;">
                                <option value="+254" selected>🇰🇪 +254</option>
                                <option value="+255">🇹🇿 +255</option>
                                <option value="+256">🇺🇬 +256</option>
                                <option value="+250">🇷🇼 +250</option>
                                <option value="+257">🇧🇮 +257</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+91">🇮🇳 +91</option>
                                <option value="+27">🇿🇦 +27</option>
                            </select>
                            <input type="text" class="form-control @error('contact') is-invalid @enderror" id="phone_number" placeholder="712345678" pattern="[0-9]{9,12}" value="{{ old('contact') }}">
                            <input type="hidden" id="contact" name="contact" value="{{ old('contact') }}" required>
                        </div>
                        <small class="text-muted">Enter phone number without leading zero</small>
                        @error('contact')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" id="department" name="department" value="{{ old('department') }}">
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Create Contact</button>
                            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-format phone number with country code
document.addEventListener('DOMContentLoaded', function() {
    const countryCode = document.getElementById('country_code');
    const phoneNumber = document.getElementById('phone_number');
    const contactHidden = document.getElementById('contact');
    
    function updateFullNumber() {
        let number = phoneNumber.value.trim();
        // Remove leading zero if present
        number = number.replace(/^0+/, '');
        // Remove any non-numeric characters
        number = number.replace(/\D/g, '');
        
        if (number) {
            contactHidden.value = countryCode.value + number;
        } else {
            contactHidden.value = '';
        }
    }
    
    // Parse existing value if editing
    const existingValue = phoneNumber.value;
    if (existingValue) {
        // Check if it starts with +
        if (existingValue.startsWith('+')) {
            // Extract country code and number
            for (let option of countryCode.options) {
                if (existingValue.startsWith(option.value)) {
                    countryCode.value = option.value;
                    phoneNumber.value = existingValue.substring(option.value.length);
                    break;
                }
            }
        }
    }
    
    countryCode.addEventListener('change', updateFullNumber);
    phoneNumber.addEventListener('input', updateFullNumber);
    
    // Initial update
    updateFullNumber();
});
</script>
@endpush

