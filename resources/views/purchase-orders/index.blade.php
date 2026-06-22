<x-app-layout title="Purchase Orders">

    @push('styles')
        <x-module.page-index-styles />
        @include('purchase-orders.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','status','delivery','supplier_id','sort']) ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Purchase Orders</h1>
                    <p class="mt-0.5 text-sm text-gray-500">POs generated from approved quotation series — track delivery and goods receipt</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('goods-receipts.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-truck-ramp-box text-xs"></i> Goods Receipts
                </a>
                <a href="{{ route('quotation-series.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-folder-open text-xs"></i> Quotation Series
                </a>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="po-kpi-grid">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total POs</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="po-kpi-sub">{{ $stats['this_month'] }} created this month</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-file-invoice"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Total Value</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['total_value'] / 1000000, 2) }}M</p>
                    <p class="po-kpi-sub">KES {{ number_format($stats['total_value'], 0) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Awaiting / In Transit</p>
                    <p class="mi-kpi-value">{{ $stats['pending'] + $stats['in_transit'] }}</p>
                    <p class="po-kpi-sub">{{ $stats['pending'] }} pending · {{ $stats['in_transit'] }} in transit</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Receipt Progress</p>
                    <p class="mi-kpi-value">{{ $stats['partial'] + $stats['received'] }}</p>
                    <p class="po-kpi-sub">{{ $stats['partial'] }} partial · {{ $stats['received'] }} complete</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-box-open"></i></div>
            </div>
        </div>

        {{-- Pipeline strip --}}
        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Delivery Pipeline</p>
            <div class="po-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $params = request()->except('page');
                        if ($step['param'] === 'delivery') {
                            unset($params['status']);
                            if ($step['key'] === '') {
                                unset($params['delivery']);
                            } else {
                                $params['delivery'] = $step['key'];
                            }
                            $isActive = ! request('status') && request('delivery', '') === $step['key'];
                        } else {
                            unset($params['delivery']);
                            $params['status'] = $step['key'];
                            $isActive = request('status') === $step['key'];
                        }
                    @endphp
                    <a href="{{ route('purchase-orders.index', $params) }}"
                       class="po-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="po-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="po-pipe-count">{{ $step['count'] }}</span>
                        <span class="po-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Filters --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if (request()->hasAny(['search','status','delivery','supplier_id']))
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
                                   placeholder="PO #, series, supplier…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-traffic-light"></i> PO Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All statuses</option>
                            <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                            <option value="partially_received" @selected(request('status') === 'partially_received')>Partially Received</option>
                            <option value="received" @selected(request('status') === 'received')>Fully Received / Closed Short</option>
                            <option value="closed_short" @selected(request('status') === 'closed_short')>Closed Short Only</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-truck-fast"></i> Delivery</label>
                        <select name="delivery" class="mi-select">
                            <option value="">All delivery states</option>
                            <option value="pending" @selected(request('delivery') === 'pending')>Pending</option>
                            <option value="in_transit" @selected(request('delivery') === 'in_transit')>In Transit</option>
                            <option value="delivered" @selected(request('delivery') === 'delivered')>Delivered</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-building"></i> Supplier</label>
                        <select name="supplier_id" class="mi-select">
                            <option value="">All suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="total" @selected(request('sort') === 'total')>Highest total</option>
                            <option value="supplier" @selected(request('sort') === 'supplier')>Supplier A–Z</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('purchase-orders.index') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $orders->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $orders->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $orders->total() }}</strong> purchase orders
                </p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Quotation Series</th>
                            <th>Supplier</th>
                            <th>Total</th>
                            <th>Receipt</th>
                            <th>Status</th>
                            <th>Delivery</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <div class="po-cell-main">
                                        <div class="po-row-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                        <div>
                                            <a href="{{ route('purchase-orders.show', $order) }}" class="mi-pkg-name hover:text-blue-600">{{ $order->po_number }}</a>
                                            <p class="mi-pkg-sub">{{ $order->items_count }} line items</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($order->quotationSeries)
                                        <a href="{{ route('quotation-series.show', $order->quotationSeries) }}" class="text-sm font-medium text-gray-700 hover:text-orange-600">
                                            {{ $order->quotationSeries->displayName() }}
                                        </a>
                                        <p class="mi-pkg-sub">{{ $order->quotationSeries->series_number }}</p>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm font-medium text-gray-700">{{ $order->supplier?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="po-cost">{{ number_format($order->total, 2) }}</span>
                                    <p class="mi-pkg-sub">{{ $order->currency }}</p>
                                </td>
                                <td>
                                    @php $pct = $order->receiptProgressPercent(); @endphp
                                    <div class="flex items-center gap-2 min-w-[5rem]">
                                        <div class="po-progress flex-1"><div class="po-progress-bar" style="width: {{ $pct }}%"></div></div>
                                        <span class="text-xs font-semibold text-gray-600">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td>@include('purchase-orders.partials.status-badge', ['order' => $order])</td>
                                <td>@include('purchase-orders.partials.delivery-badge', ['order' => $order])</td>
                                <td>
                                    <span class="text-sm text-gray-700">{{ $order->order_date?->format('d M Y') ?? '—' }}</span>
                                    @if ($order->expected_date)
                                        <p class="mi-pkg-sub">Exp. {{ $order->expected_date->format('d M') }}</p>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="mi-action view" title="View PO">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="text-center py-14">
                                        <div class="po-empty-icon"><i class="fas fa-file-invoice"></i></div>
                                        <p class="text-sm font-semibold text-gray-600">No purchase orders yet</p>
                                        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                                            POs are created when an approved quotation series is sent to the supplier.
                                        </p>
                                        <a href="{{ route('quotation-series.index') }}" class="mi-btn-ghost mt-4 inline-flex">
                                            <i class="fas fa-folder-open text-xs"></i> View Quotation Series
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="mi-card-foot">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
