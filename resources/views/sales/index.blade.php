<x-app-layout title="Sales">

    @push('styles')
        <x-module.page-index-styles />
        @include('sales.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','status','shop_id','sort']) ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-receipt"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Sales</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Order entry → cash desk checkout → completed receipts</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('sales.hold')
                    <a href="{{ route('sales.order') }}" class="mi-btn-ghost">
                        <i class="fas fa-cart-shopping text-xs"></i> Order Entry
                    </a>
                @endcan
                @can('sales.create')
                    <a href="{{ route('sales.desk') }}" class="mi-btn-orange">
                        <i class="fas fa-cash-register text-xs"></i> Cash Desk
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPI row 1: volume & revenue --}}
        <div class="sl-kpi-grid">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Transactions</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="sl-kpi-sub">{{ $stats['completed'] }} completed · {{ $stats['held'] }} at desk</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Today's Revenue</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['today_total'], 0) }}</p>
                    <p class="sl-kpi-sub">{{ $stats['today_count'] }} sale{{ $stats['today_count'] === 1 ? '' : 's' }} · avg {{ number_format($stats['avg_ticket_today'], 0) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">This Month</p>
                    <p class="mi-kpi-value">{{ number_format($stats['month_total'], 0) }}</p>
                    <p class="sl-kpi-sub">{{ $stats['month_count'] }} completed sale{{ $stats['month_count'] === 1 ? '' : 's' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">At Cash Desk</p>
                    <p class="mi-kpi-value">{{ $stats['held'] }}</p>
                    <p class="sl-kpi-sub">{{ $stats['reversed'] }} reversed</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>

        {{-- Pipeline strip --}}
        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Sales Pipeline</p>
            <div class="sl-pipeline">
                @foreach ($pipeline as $step)
                    <a href="{{ route('sales.index', array_merge(request()->except('page'), ['status' => $step['key']])) }}"
                       class="sl-pipe-step {{ request('status', '') === $step['key'] ? 'active' : '' }}">
                        <div class="sl-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="sl-pipe-count">{{ $step['count'] }}</span>
                        <span class="sl-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Charts --}}
        <div class="sl-analytics">
            <div class="mi-card sl-chart-card">
                <p class="sl-chart-title"><i class="fas fa-chart-column"></i> Revenue (6 months)</p>
                <div class="sl-chart-wrap"><canvas id="slMonthlyChart"></canvas></div>
            </div>
            <div class="mi-card sl-chart-card">
                <p class="sl-chart-title"><i class="fas fa-chart-pie"></i> By Status</p>
                <div class="sl-chart-wrap sm"><canvas id="slStatusChart"></canvas></div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if (request()->hasAny(['search','status','shop_id','sort']))
                        <span class="mi-cat-badge">Active</span>
                    @endif
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition>
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Receipt, customer, phone…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-traffic-light"></i> Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All statuses</option>
                            @foreach ($statusBreakdown as $row)
                                <option value="{{ $row['status'] }}" @selected(request('status') === $row['status'])>
                                    {{ $row['label'] }} ({{ $row['count'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if ($shops->count() > 1)
                        <div class="mi-filter-field">
                            <label class="mi-field-label"><i class="fas fa-store"></i> Shop</label>
                            <select name="shop_id" class="mi-select">
                                <option value="">All shops</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" @selected(request('shop_id') == $shop->id)>{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="total" @selected(request('sort') === 'total')>Highest total</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('sales.index') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $sales->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $sales->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $sales->total() }}</strong> transactions
                </p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Shop</th>
                            <th>Customer</th>
                            <th>Lines</th>
                            <th>Total</th>
                            <th>Staff</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            @php
                                $rowUrl = $sale->status === 'held' && auth()->user()->can('sales.create')
                                    ? route('sales.desk.checkout', $sale)
                                    : route('sales.show', $sale);
                            @endphp
                            <tr class="sl-index-row" onclick="window.location='{{ $rowUrl }}'">
                                <td>
                                    <div class="sl-sale-cell">
                                        <div class="sl-sale-icon"><i class="fas fa-receipt"></i></div>
                                        <div>
                                            <a href="{{ $rowUrl }}" class="sl-sale-ref" onclick="event.stopPropagation()">{{ $sale->receipt_number }}</a>
                                            @if ($sale->submitted_at)
                                                <p class="sl-sale-sub">Sent {{ $sale->submitted_at->diffForHumans() }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="mi-dest"><i class="fas fa-store"></i> {{ $sale->shop?->name ?? '—' }}</span>
                                </td>
                                <td class="text-sm text-gray-700">{{ $sale->customer_name ?? 'Walk-in' }}</td>
                                <td><span class="mi-bookings"><i class="fas fa-list"></i> {{ $sale->items_count }}</span></td>
                                <td><span class="sl-total">{{ number_format($sale->total, 2) }}</span></td>
                                <td class="text-sm text-gray-500">
                                    @if ($sale->orderedBy)
                                        <span class="block text-xs"><i class="fas fa-user text-gray-300 mr-1"></i>{{ $sale->orderedBy->name }}</span>
                                    @endif
                                    @if ($sale->completedBy)
                                        <span class="block text-xs text-green-600"><i class="fas fa-check text-green-400 mr-1"></i>{{ $sale->completedBy->name }}</span>
                                    @elseif (! $sale->orderedBy)
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>@include('sales.partials.status-badge', ['sale' => $sale])</td>
                                <td class="text-sm text-gray-500 whitespace-nowrap">{{ ($sale->sold_at ?? $sale->created_at)->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ $rowUrl }}" class="mi-action view" title="View sale" onclick="event.stopPropagation()">
                                        <i class="fas {{ $sale->status === 'held' && auth()->user()->can('sales.create') ? 'fa-cash-register' : 'fa-eye' }}"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="!py-16 text-center">
                                    <div class="sl-empty-icon"><i class="fas fa-receipt"></i></div>
                                    <p class="font-semibold text-gray-600">No sales found</p>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting filters or start a new order.</p>
                                    <div class="flex flex-wrap justify-center gap-2 mt-4">
                                        @can('sales.hold')
                                            <a href="{{ route('sales.order') }}" class="mi-btn-ghost inline-flex">
                                                <i class="fas fa-cart-shopping text-xs"></i> Order Entry
                                            </a>
                                        @endcan
                                        @can('sales.create')
                                            <a href="{{ route('sales.desk') }}" class="mi-btn-orange inline-flex">
                                                <i class="fas fa-cash-register text-xs"></i> Cash Desk
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($sales->hasPages())
                <div class="mi-card-foot">{{ $sales->links() }}</div>
            @endif
            <div class="sl-index-hint">
                <i class="fas fa-circle-info"></i>
                Held orders open at the cash desk for checkout. Click any row to view or complete.
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const chartData = @json($chartData);
                const palette = ['#f59e0b', '#22c55e', '#f43f5e', '#94a3b8'];

                Chart.defaults.font.family = "'Figtree', system-ui, sans-serif";
                Chart.defaults.font.size = 11;
                Chart.defaults.color = '#9ca3af';

                new Chart(document.getElementById('slMonthlyChart'), {
                    type: 'bar',
                    data: {
                        labels: chartData.monthly.labels,
                        datasets: [
                            {
                                label: 'Completed sales',
                                data: chartData.monthly.counts,
                                backgroundColor: 'rgba(255, 107, 53, 0.75)',
                                borderRadius: 6,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Revenue (KES)',
                                data: chartData.monthly.revenue,
                                type: 'line',
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139, 92, 246, 0.08)',
                                borderWidth: 2,
                                tension: 0.35,
                                pointRadius: 3,
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } },
                        },
                        scales: {
                            y:  { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1 } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false },
                                   ticks: { callback: v => (v / 1000).toFixed(0) + 'K' } },
                            x:  { grid: { display: false } },
                        },
                    },
                });

                new Chart(document.getElementById('slStatusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: chartData.status.labels,
                        datasets: [{
                            data: chartData.status.counts,
                            backgroundColor: palette,
                            borderWidth: 2,
                            borderColor: '#fff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
