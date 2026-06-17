@props([
    'icon' => 'fa-circle',
    'color' => 'text-slate-400',
    'label' => '',
])

<span class="flex cursor-not-allowed items-center gap-3 border-l-[3px] border-transparent px-4 py-2.5 text-slate-500"
      title="Coming soon">
    <i class="fas {{ $icon }} w-5 text-center {{ $color }} opacity-60"></i>
    <span class="opacity-70">{{ $label }}</span>
    <span class="ml-auto rounded bg-white/5 px-1.5 py-0.5 text-[0.55rem] font-semibold uppercase tracking-wide text-slate-400">soon</span>
</span>
