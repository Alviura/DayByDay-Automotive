@props(['location', 'locationType', 'totals', 'balances', 'lowStock'])

@php
    $inventoryFilters = ['location_type' => $locationType, 'location_id' => $location->id];
@endphp

<div class="space-y-5">
    <div class="mi-kpi-row !grid-cols-2 lg:!grid-cols-4">
        <div class="mi-kpi mi-kpi-green">
            <div>
                <p class="mi-kpi-label">On Hand</p>
                <p class="mi-kpi-value">{{ number_format($totals['on_hand'], 0) }}</p>
                <p class="inv-kpi-sub">{{ number_format($totals['available'], 0) }} available</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-purple">
            <div>
                <p class="mi-kpi-label">SKUs</p>
                <p class="mi-kpi-value">{{ number_format($totals['sku_count']) }}</p>
                <p class="inv-kpi-sub">Products with stock</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-amber">
            <div>
                <p class="mi-kpi-label">Reserved</p>
                <p class="mi-kpi-value">{{ number_format($totals['reserved'], 0) }}</p>
                <p class="inv-kpi-sub">Held for sales / transfers</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-lock"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-orange">
            <div>
                <p class="mi-kpi-label">Stock Value</p>
                <p class="mi-kpi-value orange" style="font-size:1.05rem">{{ number_format($totals['value'], 2) }}</p>
                <p class="inv-kpi-sub">Weighted average cost</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>

    @if ($totals['low_stock_count'] > 0)
        <div class="inv-phase-banner inv-phase-banner-blue" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border-color:#fcd34d;color:#92400e">
            <i class="fas fa-triangle-exclamation" style="color:#d97706"></i>
            <div>
                <strong>{{ $totals['low_stock_count'] }} low-stock SKU{{ $totals['low_stock_count'] === 1 ? '' : 's' }}</strong>
                at this location — at or below reorder level.
            </div>
        </div>
    @endif

    @if ($lowStock->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="inv-section-title"><i class="fas fa-bell"></i> Low Stock</p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>On Hand</th>
                            <th>Reorder</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStock as $balance)
                            <tr>
                                <td>
                                    <p class="font-medium text-sm">{{ $balance->product->part_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $balance->product->name }}</p>
                                </td>
                                <td class="font-semibold text-amber-700">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                                <td>{{ number_format($balance->product->reorder_level, 0) }}</td>
                                <td>
                                    @can('products.view')
                                        <a href="{{ route('products.show', $balance->product) }}" class="text-xs text-orange-600 font-semibold hover:underline">View product</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mi-card">
        <div class="mi-card-head">
            <div class="flex items-center justify-between w-full gap-3">
                <div>
                    <p class="inv-section-title"><i class="fas fa-boxes-stacked"></i> Stock by Product</p>
                    <p class="inv-section-sub">All balances at {{ $location->name }}</p>
                </div>
                @can('inventory.view')
                    <a href="{{ route('inventory.valuation', $inventoryFilters) }}" class="text-xs text-orange-600 font-semibold hover:underline">Full valuation</a>
                @endcan
            </div>
        </div>
        <div class="mi-table-wrap">
            <table class="mi-table text-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>On Hand</th>
                        <th>Reserved</th>
                        <th>Available</th>
                        <th>Avg Cost</th>
                        <th>Value</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($balances->where('quantity_on_hand', '>', 0) as $balance)
                        <tr>
                            <td>
                                <p class="font-medium text-sm">{{ $balance->product->part_number }}</p>
                                <p class="text-xs text-gray-500">{{ $balance->product->name }}</p>
                            </td>
                            <td class="font-medium">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                            <td>{{ number_format($balance->quantity_reserved, 0) }}</td>
                            <td>{{ number_format($balance->quantity_available, 0) }}</td>
                            <td>{{ number_format($balance->average_cost, 2) }}</td>
                            <td class="font-semibold text-orange-700">{{ number_format($balance->stockValue(), 2) }}</td>
                            <td>
                                @can('products.view')
                                    <a href="{{ route('products.show', $balance->product) }}" class="mi-action view"><i class="fas fa-eye"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="!py-10 text-center text-gray-400">No stock recorded at this location yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
