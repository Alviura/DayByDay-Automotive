<x-app-layout title="Quotation Series">

    @push('styles')
        <x-module.page-index-styles />
        <style>
            .qs-kpi-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: .85rem;
            }
            @media (max-width: 1100px) { .qs-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (max-width: 520px)  { .qs-kpi-grid { grid-template-columns: 1fr; } }

            .qs-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

            .qs-analytics {
                display: grid;
                grid-template-columns: 1.4fr 1fr;
                gap: 1rem;
            }
            @media (max-width: 1024px) { .qs-analytics { grid-template-columns: 1fr; } }

            .qs-chart-card { padding: 1.15rem 1.25rem 1rem; }
            .qs-chart-title {
                font-size: .82rem; font-weight: 700; color: #374151;
                display: flex; align-items: center; gap: .45rem; margin-bottom: .85rem;
            }
            .qs-chart-title i { color: #9ca3af; font-size: .75rem; }
            .qs-chart-wrap { position: relative; height: 220px; }
            .qs-chart-wrap.sm { height: 180px; }

            .qs-pipeline {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: .5rem;
            }
            @media (max-width: 900px) { .qs-pipeline { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
            @media (max-width: 520px)  { .qs-pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

            .qs-pipe-step {
                display: flex; flex-direction: column; align-items: center; text-align: center;
                padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
                background: #fafafa; text-decoration: none; color: inherit;
                transition: all .15s;
            }
            .qs-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
            .qs-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
            .qs-pipe-icon {
                width: 2rem; height: 2rem; border-radius: 8px;
                background: #fff; border: 1px solid #e5e7eb;
                display: flex; align-items: center; justify-content: center;
                font-size: .75rem; color: #ff6b35; margin-bottom: .35rem;
            }
            .qs-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
            .qs-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

            .qs-badge {
                display: inline-flex; align-items: center; gap: .3rem;
                border-radius: 9999px; padding: .22rem .65rem;
                font-size: .68rem; font-weight: 700; letter-spacing: .02em;
                white-space: nowrap;
            }
            .qs-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
            .qs-badge-violet  { background: #f3e8ff; color: #7c3aed; } .qs-badge-violet::before  { background: #8b5cf6; }
            .qs-badge-amber   { background: #fef3c7; color: #b45309; } .qs-badge-amber::before   { background: #f59e0b; }
            .qs-badge-orange  { background: #ffedd5; color: #c2410c; } .qs-badge-orange::before  { background: #ff6b35; }
            .qs-badge-blue    { background: #dbeafe; color: #1d4ed8; } .qs-badge-blue::before    { background: #3b82f6; }
            .qs-badge-indigo  { background: #e0e7ff; color: #4338ca; } .qs-badge-indigo::before  { background: #6366f1; }
            .qs-badge-cyan    { background: #cffafe; color: #0e7490; } .qs-badge-cyan::before    { background: #06b6d4; }
            .qs-badge-green   { background: #dcfce7; color: #15803d; } .qs-badge-green::before   { background: #22c55e; }
            .qs-badge-slate   { background: #f1f5f9; color: #475569; } .qs-badge-slate::before   { background: #94a3b8; }
            .qs-badge-red     { background: #fee2e2; color: #b91c1c; } .qs-badge-red::before     { background: #ef4444; }

            .qs-type-local  { background: #ecfdf5 !important; color: #047857 !important; }
            .qs-type-import { background: #eff6ff !important; color: #1d4ed8 !important; }

            .qs-series-cell { display: flex; align-items: flex-start; gap: .65rem; }
            .qs-series-icon {
                width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
                background: linear-gradient(135deg, #fff7ed, #ffedd5);
                border: 1px solid #fed7aa; color: #ea580c;
                display: flex; align-items: center; justify-content: center; font-size: .75rem;
            }
            .qs-cost { font-weight: 700; color: #111827; white-space: nowrap; }
            .qs-margin-pos { color: #059669; font-weight: 600; font-size: .78rem; }
            .qs-margin-neg { color: #dc2626; font-weight: 600; font-size: .78rem; }
            .qs-empty-icon {
                width: 3.5rem; height: 3.5rem; border-radius: 50%;
                background: #f3f4f6; color: #d1d5db;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.25rem; margin: 0 auto 1rem;
            }
        </style>
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','status','supplier_id','purchase_type','sort']) ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Quotation Series</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Procurement pipeline — quotation → order → PO → receipt → close</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchase-orders.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-file-invoice text-xs"></i> Purchase Orders
                </a>
                @can('procurement.manage')
                    <a href="{{ route('quotation-series.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i> New Series
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPI row 1: volume --}}
        <div class="qs-kpi-grid">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Series</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="qs-kpi-sub">{{ $stats['open'] }} open · {{ $stats['this_month'] }} this month</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-layer-group"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Total Actual Cost</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['total_cost'] / 1000000, 2) }}M</p>
                    <p class="qs-kpi-sub">KES {{ number_format($stats['total_cost'], 0) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Expected Margin</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total_margin'] / 1000000, 2) }}M</p>
                    <p class="qs-kpi-sub">KES {{ number_format($stats['total_margin'], 0) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Local / Import</p>
                    <p class="mi-kpi-value">{{ $stats['local'] }} / {{ $stats['import'] }}</p>
                    <p class="qs-kpi-sub">{{ $stats['local'] + $stats['import'] > 0 ? round($stats['import'] / ($stats['local'] + $stats['import']) * 100) : 0 }}% import</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-globe"></i></div>
            </div>
        </div>

        {{-- KPI row 2: pipeline counts --}}
        <div class="qs-kpi-grid">
            <div class="mi-kpi mi-kpi-amber">
                <div><p class="mi-kpi-label">Quotation Drafts</p><p class="mi-kpi-value">{{ $stats['quotation_draft'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-pen"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div><p class="mi-kpi-label">Order Drafts</p><p class="mi-kpi-value orange">{{ $stats['order_draft'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-calculator"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div><p class="mi-kpi-label">Approved / PO</p><p class="mi-kpi-value">{{ $stats['approved'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-file-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div><p class="mi-kpi-label">In Transit</p><p class="mi-kpi-value">{{ $stats['in_transit'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>

        {{-- Pipeline strip --}}
        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Workflow Pipeline</p>
            <div class="qs-pipeline">
                @foreach ($pipeline as $step)
                    <a href="{{ route('quotation-series.index', array_merge(request()->except('page'), ['status' => $step['key']])) }}"
                       class="qs-pipe-step {{ request('status') === $step['key'] ? 'active' : '' }}">
                        <div class="qs-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="qs-pipe-count">{{ $step['count'] }}</span>
                        <span class="qs-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Charts --}}
        <div class="qs-analytics">
            <div class="mi-card qs-chart-card">
                <p class="qs-chart-title"><i class="fas fa-chart-column"></i> Series Activity (6 months)</p>
                <div class="qs-chart-wrap"><canvas id="qsMonthlyChart"></canvas></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mi-card qs-chart-card">
                    <p class="qs-chart-title"><i class="fas fa-chart-pie"></i> By Status</p>
                    <div class="qs-chart-wrap sm"><canvas id="qsStatusChart"></canvas></div>
                </div>
                <div class="mi-card qs-chart-card">
                    <p class="qs-chart-title"><i class="fas fa-earth-africa"></i> Local vs Import</p>
                    <div class="qs-chart-wrap sm"><canvas id="qsTypeChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if (request()->hasAny(['search','status','supplier_id','purchase_type']))
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
                                   placeholder="Series, reference, supplier…" class="mi-input">
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
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-truck"></i> Supplier</label>
                        <select name="supplier_id" class="mi-select">
                            <option value="">All suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-globe"></i> Purchase Type</label>
                        <select name="purchase_type" class="mi-select">
                            <option value="">All types</option>
                            <option value="local" @selected(request('purchase_type') === 'local')>Local</option>
                            <option value="import" @selected(request('purchase_type') === 'import')>Import</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="cost" @selected(request('sort') === 'cost')>Highest cost</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('quotation-series.index') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $seriesList->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $seriesList->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $seriesList->total() }}</strong> series
                </p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Series</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Lines</th>
                            <th>Actual Cost</th>
                            <th>Exp. Margin</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($seriesList as $series)
                            @php
                                $cost = (float) ($series->total_actual_cost ?: $series->total_landing_cost);
                                $margin = (float) $series->total_expected_margin;
                            @endphp
                            <tr>
                                <td>
                                    <div class="qs-series-cell">
                                        <div class="qs-series-icon"><i class="fas fa-folder-open"></i></div>
                                        <div>
                                            <a href="{{ route('quotation-series.show', $series) }}" class="mi-pkg-name hover:text-orange-600">{{ $series->displayName() }}</a>
                                            <p class="mi-pkg-sub">{{ $series->series_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="mi-dest"><i class="fas fa-truck"></i> {{ $series->supplier?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="mi-cat-badge {{ ($series->purchase_type ?? 'local') === 'import' ? 'qs-type-import' : 'qs-type-local' }}">
                                        {{ ucfirst($series->purchase_type ?? 'local') }}
                                    </span>
                                </td>
                                <td><span class="mi-bookings"><i class="fas fa-list"></i> {{ $series->items_count }}</span></td>
                                <td><span class="qs-cost">{{ number_format($cost, 0) }} <span class="text-xs text-gray-400 font-normal">KES</span></span></td>
                                <td>
                                    @if ($margin != 0)
                                        <span class="{{ $margin >= 0 ? 'qs-margin-pos' : 'qs-margin-neg' }}">
                                            {{ $margin >= 0 ? '+' : '' }}{{ number_format($margin, 0) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>@include('quotation-series.partials.status-badge', ['series' => $series])</td>
                                <td class="text-sm text-gray-500 whitespace-nowrap">{{ $series->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('quotation-series.show', $series) }}" class="mi-action view" title="View series"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="!py-16 text-center">
                                    <div class="qs-empty-icon"><i class="fas fa-folder-open"></i></div>
                                    <p class="font-semibold text-gray-600">No quotation series found</p>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting filters or create a new series.</p>
                                    @can('procurement.manage')
                                        <a href="{{ route('quotation-series.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                            <i class="fas fa-plus text-xs"></i> New Series
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($seriesList->hasPages())
                <div class="mi-card-foot">{{ $seriesList->links() }}</div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const chartData = @json($chartData);
                const palette = ['#ff6b35','#8b5cf6','#f59e0b','#22c55e','#3b82f6','#06b6d4','#6366f1','#94a3b8','#ef4444','#ec4899'];

                Chart.defaults.font.family = "'Figtree', system-ui, sans-serif";
                Chart.defaults.font.size = 11;
                Chart.defaults.color = '#9ca3af';

                new Chart(document.getElementById('qsMonthlyChart'), {
                    type: 'bar',
                    data: {
                        labels: chartData.monthly.labels,
                        datasets: [
                            {
                                label: 'Series created',
                                data: chartData.monthly.counts,
                                backgroundColor: 'rgba(255, 107, 53, 0.75)',
                                borderRadius: 6,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Actual cost (KES)',
                                data: chartData.monthly.values,
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

                new Chart(document.getElementById('qsStatusChart'), {
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

                new Chart(document.getElementById('qsTypeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: chartData.types.labels,
                        datasets: [{
                            data: chartData.types.counts,
                            backgroundColor: ['#22c55e', '#3b82f6'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10 } },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
