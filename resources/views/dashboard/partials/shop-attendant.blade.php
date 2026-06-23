@include('dashboard.partials.command-strip', ['strip' => $commandStrip ?? ['cards' => [], 'summary' => []]])

@if (count($quickActions) > 0)
    <div class="db-attendant-actions">
        @foreach ($quickActions as $action)
            @if (in_array($action['label'], ['Cash Desk', 'Order Entry'], true))
                <a href="{{ $action['url'] }}" class="db-attendant-btn {{ ($action['variant'] ?? '') === 'primary' ? 'db-attendant-btn--primary' : '' }}">
                    <i class="fas {{ $action['icon'] }}"></i>
                    <span>{{ $action['label'] }}</span>
                    <small>{{ $action['desc'] }}</small>
                </a>
            @endif
        @endforeach
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    @include('dashboard.partials.held-sales', ['sales' => $heldSales])
    @include('dashboard.partials.recent-sales', ['sales' => $recentSales, 'title' => 'Completed Today'])
</div>
