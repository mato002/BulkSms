@props([
    'href',
    'frame' => 'main-content',
    'active' => false,
    'tooltip' => null,
])

<a
    href="{{ $href }}"
    {{ $attributes->class(['sidebar-nav-item', 'active' => $active]) }}
    data-turbo-frame="{{ $frame }}"
    data-turbo-action="advance"
    @if($tooltip)
        data-bs-toggle="tooltip"
        data-bs-placement="right"
        title="{{ $tooltip }}"
    @endif
>
    {{ $slot }}
</a>
