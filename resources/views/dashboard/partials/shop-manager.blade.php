<div class="db-role-shell">
@include('dashboard.partials.command-strip', ['strip' => $commandStrip ?? ['cards' => [], 'summary' => []]])

@if ($showChart ?? false)
    <div class="db-layout db-layout--with-side">
        <div class="db-main">
            <div class="mi-card p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Sales Trend — Last 6 Months</p>
                <div class="db-chart-wrap"><canvas id="dbSalesChart"></canvas></div>
            </div>
        </div>

        <div class="db-side">
            @include('dashboard.partials.quick-actions', ['actions' => $quickActions])

            @if ($location ?? null)
                <div class="mi-card p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Your Location</p>
                    <p class="font-semibold text-gray-900">{{ $location->name }}</p>
                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $location->code }}</p>
                </div>
            @endif
        </div>
    </div>
@else
    @include('dashboard.partials.quick-actions', ['actions' => $quickActions])

    @if ($location ?? null)
        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Your Location</p>
            <p class="font-semibold text-gray-900">{{ $location->name }}</p>
            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $location->code }}</p>
        </div>
    @endif
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    @include('dashboard.partials.held-sales', ['sales' => $heldSales])
    @include('dashboard.partials.recent-sales', ['sales' => $recentSales])
</div>

@if (($lowStock ?? collect())->isNotEmpty())
    @include('dashboard.partials.low-stock', ['items' => $lowStock])
@endif
</div>

@include('dashboard.partials.sales-chart')
