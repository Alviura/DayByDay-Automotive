@props(['actions' => []])

@if (count($actions) > 0)
    <div class="mi-card p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Quick Actions</p>
        <div class="db-quick-grid">
            @foreach ($actions as $action)
                <a href="{{ $action['url'] }}" class="db-quick {{ ($action['variant'] ?? '') === 'primary' ? 'db-quick--primary' : '' }}">
                    <div class="db-quick-icon"><i class="fas {{ $action['icon'] }}"></i></div>
                    <div class="min-w-0">
                        <p class="db-quick-title">{{ $action['label'] }}</p>
                        <p class="db-quick-desc">{{ $action['desc'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
