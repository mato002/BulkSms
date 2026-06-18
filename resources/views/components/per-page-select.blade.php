@props(['current' => 25])

<select name="per_page" class="{{ $attributes->get('class', 'form-select form-select-sm') }}">
    @foreach([10, 25, 50, 100] as $size)
        <option value="{{ $size }}" {{ (int) $current === $size ? 'selected' : '' }}>{{ $size }} / page</option>
    @endforeach
</select>
