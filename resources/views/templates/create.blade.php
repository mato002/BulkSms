@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-file-text me-2"></i>Create Template</h1>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('templates.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Template Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="channel" class="form-label">Channel *</label>
                        <select class="form-select @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                            <option value="">Select Channel</option>
                            <option value="sms" {{ old('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                            <option value="whatsapp" {{ old('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                        @error('channel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12" id="subject-field">
                        <label for="subject" class="form-label">Subject (Email/WhatsApp)</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="body" class="form-label">Message Body *</label>
                        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="8" required>{{ old('body') }}</textarea>
                        <div class="form-text">Use variables like @{{ name }}, @{{ phone }}. Variables must exist in contact fields.</div>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Create Template</button>
                        <a href="{{ route('templates.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('channel').addEventListener('change', function() {
    const subjectField = document.getElementById('subject-field');
    if (this.value === 'sms') {
        subjectField.style.display = 'none';
    } else {
        subjectField.style.display = 'block';
    }
});
</script>
@endsection

