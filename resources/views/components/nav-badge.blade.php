@props(['badge' => null])

@if (($badge['count'] ?? 0) > 0)
    <span class="ml-auto rounded-full bg-orange-500 px-1.5 py-0.5 text-[.58rem] font-bold text-white">
        {{ $badge['count'] > 99 ? '99+' : $badge['count'] }}
    </span>
@endif
