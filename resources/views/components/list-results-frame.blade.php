@props(['id' => 'list-results'])

<div {{ $attributes->merge(['id' => $id, 'class' => 'list-results-region']) }}>
    {{ $slot }}
</div>
