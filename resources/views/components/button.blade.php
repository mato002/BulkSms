@props(['type' => 'button', 'variant' => 'primary', 'size' => 'md', 'disabled' => false])

@php
$classes = [
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'success' => 'btn-success',
    'danger' => 'btn-danger',
    'warning' => 'btn-warning',
    'info' => 'btn-info',
    'light' => 'btn-light',
    'dark' => 'btn-dark',
    'outline-primary' => 'btn-outline-primary',
    'outline-secondary' => 'btn-outline-secondary',
    'outline-success' => 'btn-outline-success',
    'outline-danger' => 'btn-outline-danger',
    'outline-warning' => 'btn-outline-warning',
    'outline-info' => 'btn-outline-info',
    'outline-light' => 'btn-outline-light',
    'outline-dark' => 'btn-outline-dark',
][$variant] ?? 'btn-primary';

$sizeClasses = [
    'sm' => 'btn-sm',
    'md' => '',
    'lg' => 'btn-lg',
][$size] ?? '';
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => "btn {$classes} {$sizeClasses}"]) }}
    @if($disabled) disabled @endif
>
    {{ $slot }}
</button>



