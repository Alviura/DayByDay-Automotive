<div class="db-role-shell">
@include('dashboard.partials.command-strip', ['strip' => $commandStrip ?? ['cards' => [], 'summary' => []]])

@if (! empty($approvalPipeline))
    <div class="mi-card p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Approval Queue</p>
        <div class="db-pipeline">
            @foreach ($approvalPipeline as $step)
                <a href="{{ $step['url'] }}" class="db-pipe-chip">
                    <i class="fas {{ $step['icon'] }} text-orange-400"></i>
                    <span>{{ $step['label'] }}</span>
                    <span class="count">{{ $step['count'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif

@if ($showChart ?? false)
    <div class="db-layout db-layout--with-side">
        <div class="db-main">
            @include('dashboard.partials.charts-grid')
        </div>

        <div class="db-side">
            @include('dashboard.partials.quick-actions', ['actions' => $quickActions])
        </div>
    </div>
@endif

<div class="db-tables-mosaic">
    <div class="db-table-panel db-table-panel--wide">
        @include('dashboard.partials.recent-sales', ['sales' => $recentSales, 'showShop' => true])
    </div>
    <div class="db-table-panel db-table-panel--narrow">
        @include('dashboard.partials.pending-approvals', ['approvals' => $pendingApprovalsList ?? collect()])
    </div>
    <div class="db-table-panel db-table-panel--medium">
        @include('dashboard.partials.active-transfers', ['transfers' => $activeTransfers ?? collect()])
    </div>
    <div class="db-table-panel db-table-panel--wide">
        @include('dashboard.partials.open-procurement', ['series' => $recentProcurement ?? collect()])
    </div>
    <div class="db-table-panel db-table-panel--full">
        @include('dashboard.partials.low-stock-alerts', ['products' => $lowStockProducts ?? collect()])
    </div>
</div>
</div>

@include('dashboard.partials.admin-charts')
