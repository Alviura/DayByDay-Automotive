<x-app-layout title="Inventory">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Inventory</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Stock balances across warehouses and shops — the live snapshot from the ledger.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.movements') }}" class="mi-btn-ghost">
                    <i class="fas fa-right-left text-xs"></i> Movements
                </a>
                <a href="{{ route('inventory.valuation') }}" class="mi-btn-ghost">
                    <i class="fas fa-coins text-xs"></i> Valuation
                </a>
                @can('inventory.adjust')
                    <a href="{{ route('stock-adjustments.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i>
                        New Adjustment
                    </a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div><p class="mi-kpi-label">SKUs in Stock</p><p class="mi-kpi-value">{{ number_format($stats['skus']) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div><p class="mi-kpi-label">Units on Hand</p><p class="mi-kpi-value">{{ number_format($stats['units'], 0) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div><p class="mi-kpi-label">Reserved</p><p class="mi-kpi-value">{{ number_format($stats['reserved'], 0) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-lock"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div><p class="mi-kpi-label">Total Value</p><p class="mi-kpi-value orange">{{ number_format($stats['value'], 2) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <span class="text-sm font-semibold text-gray-700">Filters</span>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">Toggle</button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition>
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Product name or part number…">
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Location type</label>
                        <select name="location_type" class="mi-select">
                            <option value="">All</option>
                            <option value="warehouse" @selected(request('location_type') === 'warehouse')>Warehouse</option>
                            <option value="shop" @selected(request('location_type') === 'shop')>Shop</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Location</label>
                        <select name="location_id" class="mi-select">
                            <option value="">All</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" @selected(request('location_id') == $w->id && request('location_type') === 'warehouse')>WH: {{ $w->name }}</option>
                            @endforeach
                            @foreach ($shops as $s)
                                <option value="{{ $s->id }}" @selected(request('location_id') == $s->id && request('location_type') === 'shop')>SH: {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Filter</label>
                        <select name="filter" class="mi-select">
                            <option value="">All balances</option>
                            <option value="in_stock" @selected(request('filter') === 'in_stock')>In stock</option>
                            <option value="out_of_stock" @selected(request('filter') === 'out_of_stock')>Out of stock</option>
                            <option value="low_stock" @selected(request('filter') === 'low_stock')>Low stock</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('inventory.index') }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Location</th>
                            <th>On Hand</th>
                            <th>Reserved</th>
                            <th>Available</th>
                            <th>Avg Cost</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($balances as $balance)
                            <tr>
                                <td>
                                    <a href="{{ route('inventory.show', $balance->product) }}" class="mi-pkg-name hover:text-orange-600">
                                        {{ $balance->product->part_number }}
                                    </a>
                                    <p class="text-xs text-gray-500">{{ Str::limit($balance->product->name, 40) }}</p>
                                </td>
                                <td>
                                    <span class="mi-cat-badge">
                                        {{ $balance->location instanceof \App\Models\Warehouse ? 'WH' : 'SH' }}
                                        {{ $balance->location?->name }}
                                    </span>
                                </td>
                                <td class="font-medium">{{ number_format($balance->quantity_on_hand, 2) }}</td>
                                <td>{{ number_format($balance->quantity_reserved, 2) }}</td>
                                <td>{{ number_format($balance->quantity_available, 2) }}</td>
                                <td>{{ number_format($balance->average_cost, 2) }}</td>
                                <td class="font-medium">{{ number_format($balance->stockValue(), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="!py-14 text-center text-gray-400">No stock balances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($balances->hasPages())
                <div class="mi-card-foot">{{ $balances->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
