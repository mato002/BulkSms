@props(['type' => 'info', 'dismissible' => true])

@php
$classes = [
    'info' => 'alert-info',
    'success' => 'alert-success',
    'warning' => 'alert-warning',
    'danger' => 'alert-danger',
    'error' => 'alert-danger',
][$type] ?? 'alert-info';
@endphp

<div {{ $attributes->merge(['class' => "alert {$classes}" . ($dismissible ? ' alert-dismissible fade show' : '')]) }}>
    {{ $slot }}
    
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>




