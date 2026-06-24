@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-zinc-300 bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm']) !!}>
