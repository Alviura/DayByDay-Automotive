@props(['definition', 'filters', 'slug'])

<div class="flex flex-wrap items-start justify-between gap-4">
    <div class="flex items-start gap-3">
        <div class="mi-page-icon"><i class="fas {{ $definition['icon'] ?? 'fa-chart-line' }}"></i></div>
        <div>
            <h1 class="text-[1.35rem] font-bold text-gray-900">{{ $definition['label'] ?? 'Report' }}</h1>
            <p class="text-sm text-gray-500">{{ $definition['description'] ?? '' }}</p>
            @if ($filters->from ?? null)
                <p class="text-xs text-gray-400 mt-0.5">{{ $filters->from->format('d M Y') }} — {{ $filters->to->format('d M Y') }}</p>
            @endif
        </div>
    </div>
    <a href="{{ route('reports.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> All Reports</a>
</div>
