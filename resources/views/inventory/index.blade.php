<x-app-layout title="Inventory">

    @push('styles')
        <x-module.page-index-styles />
        @include('inventory.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'filter', 'location_type', 'sort']) ? 'true' : 'true' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-boxes-stacked"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Inventory</h1>
                    <p class="mt-0.5 text-sm text-gray-500">One row per product — warehouse and shop quantities combined.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.movements') }}" class="mi-btn-ghost"><i class="fas fa-right-left text-xs"></i> Movements</a>
                <a href="{{ route('inventory.valuation') }}" class="mi-btn-ghost"><i class="fas fa-coins text-xs"></i> Valuation</a>
                @can('inventory.adjust')
                    <a href="{{ route('stock-adjustments.create') }}" class="mi-btn-orange"><i class="fas fa-plus text-xs"></i> Adjustment</a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Products in Stock</p>
                    <p class="mi-kpi-value">{{ number_format($stats['skus']) }}</p>
                    <p class="inv-kpi-sub">Unique SKUs with qty &gt; 0</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Units on Hand</p>
                    <p class="mi-kpi-value">{{ number_format($stats['units'], 0) }}</p>
                    <p class="inv-kpi-sub">{{ number_format($stats['reserved'], 0) }} reserved</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Low Stock</p>
                    <p class="mi-kpi-value">{{ number_format($stats['low_stock']) }}</p>
                    <p class="inv-kpi-sub">Total qty at or below reorder</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Total Value</p>
                    <p class="mi-kpi-value orange" style="font-size:1.15rem">{{ number_format($stats['value'], 2) }}</p>
                    <p class="inv-kpi-sub">Qty × weighted avg cost</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>

        @if ($stats['incoming_units'] > 0)
            <div class="inv-phase-banner inv-phase-banner-blue">
                <i class="fas fa-truck-ramp-box"></i>
                <div>
                    <strong>{{ number_format($stats['incoming_units'], 0) }} units on order</strong>
                    from open purchase orders ({{ $stats['incoming_lines'] }} {{ str('line')->plural($stats['incoming_lines']) }}).
                    Stock will post when goods are received via GRN.
                </div>
            </div>
        @endif

        <div class="mi-card">
            <div class="mi-card-head">
                <div>
                    <p class="inv-section-title"><i class="fas fa-filter"></i> Filters</p>
                    <p class="inv-section-sub">Search, location, and stock health</p>
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-ghost text-xs">
                    <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': filtersOpen }"></i>
                </button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition class="border-t border-gray-100">
                <div class="mi-filter-grid p-4 pb-0">
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Part number or product name…">
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Location type</label>
                        <select name="location_type" class="mi-select">
                            <option value="">All types</option>
                            <option value="warehouse" @selected(request('location_type') === 'warehouse')>Warehouse</option>
                            <option value="shop" @selected(request('location_type') === 'shop')>Shop</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Location</label>
                        <select name="location_id" class="mi-select">
                            <option value="">All locations</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" @selected(request('location_id') == $w->id && request('location_type') === 'warehouse')>{{ $w->name }} (WH)</option>
                            @endforeach
                            @foreach ($shops as $s)
                                <option value="{{ $s->id }}" @selected(request('location_id') == $s->id && request('location_type') === 'shop')>{{ $s->name }} (Shop)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Stock filter</label>
                        <select name="filter" class="mi-select">
                            <option value="">All products</option>
                            <option value="in_stock" @selected(request('filter') === 'in_stock')>In stock</option>
                            <option value="low_stock" @selected(request('filter') === 'low_stock')>Low stock</option>
                            <option value="out_of_stock" @selected(request('filter') === 'out_of_stock')>Out of stock</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Sort by</label>
                        <select name="sort" class="mi-select">
                            <option value="qty" @selected(request('sort', 'qty') === 'qty')>Total quantity (high first)</option>
                            <option value="value" @selected(request('sort') === 'value')>Value (high first)</option>
                            <option value="product" @selected(request('sort') === 'product')>Product name</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions p-4">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('inventory.index') }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <div>
                    <p class="inv-section-title"><i class="fas fa-table"></i> Stock by Product</p>
                    <p class="inv-section-sub">{{ $products->total() }} {{ str('product')->plural($products->total()) }} · click any row for location breakdown</p>
                </div>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table inv-index-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>Shop</th>
                            <th>Total</th>
                            <th>Available</th>
                            <th>Value</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $totalOnHand = (float) $product->total_on_hand;
                                $warehouseQty = (float) $product->warehouse_qty;
                                $shopQty = (float) $product->shop_qty;
                                $totalValue = (float) $product->total_value;
                                $isLow = $product->reorder_level > 0
                                    && $totalOnHand > 0
                                    && $totalOnHand <= (float) $product->reorder_level;
                                $whPct = $totalOnHand > 0 ? round($warehouseQty / $totalOnHand * 100) : 0;
                                $shPct = $totalOnHand > 0 ? 100 - $whPct : 0;
                                $showUrl = route('inventory.show', $product);
                            @endphp
                            <tr class="inv-index-row" onclick="window.location='{{ $showUrl }}'">
                                <td>
                                    <div class="inv-product-cell">
                                        <div class="inv-product-thumb"><i class="fas fa-box"></i></div>
                                        <div class="inv-product-meta">
                                            <p class="inv-product-part">{{ $product->part_number }}</p>
                                            <p class="inv-product-name">{{ Str::limit($product->name, 48) }}</p>
                                            <div class="inv-product-tags">
                                                @if ($product->unit)
                                                    <span class="mi-cat-badge">{{ $product->unit->name }}</span>
                                                @endif
                                                @if ($isLow)
                                                    <span class="inv-badge inv-badge-amber">Low stock</span>
                                                @elseif ($totalOnHand <= 0)
                                                    <span class="inv-badge inv-badge-slate">Out of stock</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @include('inventory.partials.index-location-qty', [
                                        'balances' => $product->stockBalances,
                                        'totalQty' => $warehouseQty,
                                        'type' => 'warehouse',
                                    ])
                                </td>
                                <td>
                                    @include('inventory.partials.index-location-qty', [
                                        'balances' => $product->stockBalances,
                                        'totalQty' => $shopQty,
                                        'type' => 'shop',
                                    ])
                                </td>
                                <td>
                                    <div class="inv-total-cell">
                                        <p class="inv-total-qty">{{ number_format($totalOnHand, 0) }}</p>
                                        @if ($totalOnHand > 0 && ($warehouseQty > 0 || $shopQty > 0))
                                            <div class="inv-split-bar" title="Warehouse {{ $whPct }}% · Shop {{ $shPct }}%">
                                                @if ($warehouseQty > 0)
                                                    <span class="inv-split-bar-wh" style="width: {{ $whPct }}%"></span>
                                                @endif
                                                @if ($shopQty > 0)
                                                    <span class="inv-split-bar-sh" style="width: {{ $shPct }}%"></span>
                                                @endif
                                            </div>
                                        @endif
                                        @if ((float) $product->total_reserved > 0)
                                            <p class="inv-total-sub">{{ number_format($product->total_reserved, 0) }} reserved</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="font-medium">{{ number_format($product->total_available, 0) }}</td>
                                <td class="font-semibold text-orange-700">{{ number_format($totalValue, 2) }}</td>
                                <td class="text-gray-300">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!py-14 text-center">
                                    <div class="inv-empty-icon"><i class="fas fa-box-open"></i></div>
                                    <p class="text-gray-500 font-medium">No products match your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="inv-index-hint">
                <i class="fas fa-circle-info"></i>
                <span>Each product appears once. Open a row to see per-location balances, movements, and receipt history.</span>
            </div>
            @if ($products->hasPages())
                <div class="mi-card-foot">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
