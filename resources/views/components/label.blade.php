@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-medium mb-1']) }}>
    {{ $value ?? $slot }}
</label>
