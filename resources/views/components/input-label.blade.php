@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-zinc-700']) }}>
    {{ $value ?? $slot }}
</label>
