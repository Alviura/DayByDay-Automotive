<x-app-layout title="Goods Receipts">

    @push('styles')
        <x-module.page-index-styles />
        @include('goods-receipts.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','warehouse_id','damage','sort']) ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-truck-ramp-box"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Goods Receipts</h1>
                    <p class="mt-0.5 text-sm text-gray-500">GRNs posted to inventory from purchase orders</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchase-orders.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-file-invoice-dollar text-xs"></i> Purchase Orders
                </a>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="grn-kpi-grid">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Total GRNs</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="grn-kpi-sub">{{ $stats['this_month'] }} this month</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Units Received</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total_received'], 0) }}</p>
                    <p class="grn-kpi-sub">{{ number_format($stats['total_good'], 0) }} good stock</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-box-open"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Damaged Units</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total_damaged'], 0) }}</p>
                    <p class="grn-kpi-sub">{{ $stats['with_damage'] }} GRNs with damage</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Good Rate</p>
                    <p class="mi-kpi-value orange">
                        {{ $stats['total_received'] > 0 ? round($stats['total_good'] / $stats['total_received'] * 100) : 100 }}%
                    </p>
                    <p class="grn-kpi-sub">Received minus damaged</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if (request()->hasAny(['search','warehouse_id','damage']))
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
                                   placeholder="GRN #, PO #, supplier…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-warehouse"></i> Warehouse</label>
                        <select name="warehouse_id" class="mi-select">
                            <option value="">All warehouses</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-triangle-exclamation"></i> Damage</label>
                        <select name="damage" class="mi-select">
                            <option value="">All receipts</option>
                            <option value="yes" @selected(request('damage') === 'yes')>With damage</option>
                            <option value="no" @selected(request('damage') === 'no')>No damage</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="items" @selected(request('sort') === 'items')>Most line items</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('goods-receipts.index') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $receipts->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $receipts->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $receipts->total() }}</strong> receipts
                </p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>GRN</th>
                            <th>Purchase Order</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Lines</th>
                            <th>Received</th>
                            <th>Damage</th>
                            <th>Received At</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipts as $grn)
                            @php
                                $receivedQty = \App\Models\GoodsReceiptNoteItem::normalizeQuantity($grn->total_received_qty ?? 0);
                                $damagedQty = \App\Models\GoodsReceiptNoteItem::normalizeQuantity($grn->total_damaged_qty ?? 0);
                                $goodQty = max(0, round($receivedQty - $damagedQty, 2));
                            @endphp
                            <tr>
                                <td>
                                    <div class="grn-cell-main">
                                        <div class="grn-row-icon"><i class="fas fa-clipboard-check"></i></div>
                                        <div>
                                            <a href="{{ route('goods-receipts.show', $grn) }}" class="mi-pkg-name hover:text-emerald-600">{{ $grn->grn_number }}</a>
                                            <p class="mi-pkg-sub">{{ $grn->receiver?->name ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($grn->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                                            {{ $grn->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm font-medium text-gray-700">{{ $grn->purchaseOrder?->supplier?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-sm text-gray-700">{{ $grn->warehouse?->name ?? '—' }}</span>
                                    @if ($grn->warehouse?->code)
                                        <p class="mi-pkg-sub">{{ $grn->warehouse->code }}</p>
                                    @endif
                                </td>
                                <td>{{ $grn->items_count }}</td>
                                <td><span class="grn-qty-good">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($receivedQty) }}</span></td>
                                <td>
                                    @if ($damagedQty > 0)
                                        <span class="grn-damage-flag"><i class="fas fa-triangle-exclamation text-[0.55rem]"></i> {{ \App\Models\GoodsReceiptNoteItem::formatQuantity($damagedQty) }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm text-gray-700">{{ $grn->received_at?->format('d M Y') ?? '—' }}</span>
                                    @if ($grn->received_at)
                                        <p class="mi-pkg-sub">{{ $grn->received_at->format('H:i') }}</p>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('goods-receipts.show', $grn) }}" class="mi-action view" title="View GRN">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="text-center py-14">
                                        <div class="grn-empty-icon"><i class="fas fa-truck-ramp-box"></i></div>
                                        <p class="text-sm font-semibold text-gray-600">No goods receipts yet</p>
                                        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                                            GRNs are created when goods arrive against an open purchase order.
                                        </p>
                                        <a href="{{ route('purchase-orders.index') }}" class="mi-btn-ghost mt-4 inline-flex">
                                            <i class="fas fa-file-invoice-dollar text-xs"></i> View Purchase Orders
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($receipts->hasPages())
                <div class="mi-card-foot">{{ $receipts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
