@php
    $isLow = $product->reorder_level > 0
        && $totals['on_hand'] > 0
        && $totals['on_hand'] <= (float) $product->reorder_level;
    $totalOnHand = $totals['on_hand'];
    $whPct = $totalOnHand > 0 ? round($totals['warehouse_qty'] / $totalOnHand * 100) : 0;
@endphp

<div class="space-y-5">
    <div class="mi-kpi-row !grid-cols-2 lg:!grid-cols-4">
        <div class="mi-kpi mi-kpi-green">
            <div>
                <p class="mi-kpi-label">Total on Hand</p>
                <p class="mi-kpi-value">{{ number_format($totals['on_hand'], 0) }}</p>
                <p class="inv-kpi-sub">{{ number_format($totals['available'], 0) }} available</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-purple">
            <div>
                <p class="mi-kpi-label">Warehouse</p>
                <p class="mi-kpi-value">{{ number_format($totals['warehouse_qty'], 0) }}</p>
                <p class="inv-kpi-sub">All warehouse locations</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-warehouse"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-indigo" style="--kpi-accent:#6366f1">
            <div>
                <p class="mi-kpi-label">Shop</p>
                <p class="mi-kpi-value">{{ number_format($totals['shop_qty'], 0) }}</p>
                <p class="inv-kpi-sub">All shop locations</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-store"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-orange">
            <div>
                <p class="mi-kpi-label">Stock Value</p>
                <p class="mi-kpi-value orange" style="font-size:1.05rem">{{ number_format($totals['value'], 2) }}</p>
                <p class="inv-kpi-sub">
                    @if ($incoming['units'] > 0)
                        {{ number_format($incoming['units'], 0) }} on order
                    @else
                        Weighted avg cost
                    @endif
                </p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>

    @if ($isLow)
        <div class="inv-phase-banner inv-phase-banner-blue" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border-color:#fcd34d;color:#92400e">
            <i class="fas fa-triangle-exclamation" style="color:#d97706"></i>
            <div>
                <strong>Low stock</strong> — total on hand ({{ number_format($totals['on_hand'], 0) }}) is at or below the reorder level of {{ number_format($product->reorder_level, 0) }}.
            </div>
        </div>
    @endif

    @if ($totalOnHand > 0 && ($totals['warehouse_qty'] > 0 || $totals['shop_qty'] > 0))
        <div class="mi-card !p-4">
            <div class="flex items-center justify-between gap-3 mb-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock split</p>
                <p class="text-xs text-gray-400">WH {{ $whPct }}% · Shop {{ 100 - $whPct }}%</p>
            </div>
            <div class="inv-split-bar" style="max-width:none;height:8px">
                @if ($totals['warehouse_qty'] > 0)
                    <span class="inv-split-bar-wh" style="width:{{ $whPct }}%"></span>
                @endif
                @if ($totals['shop_qty'] > 0)
                    <span class="inv-split-bar-sh" style="width:{{ 100 - $whPct }}%"></span>
                @endif
            </div>
        </div>
    @endif

    <div class="mi-card">
        <div class="mi-card-head">
            <div class="flex items-center justify-between w-full gap-3">
                <div>
                    <p class="inv-section-title"><i class="fas fa-warehouse"></i> Balances by Location</p>
                    <p class="inv-section-sub">Weighted average cost per warehouse or shop</p>
                </div>
                @can('inventory.view')
                    <a href="{{ route('inventory.show', $product) }}" class="text-xs text-orange-600 font-semibold hover:underline">Inventory view</a>
                @endcan
            </div>
        </div>
        <div class="mi-table-wrap">
            <table class="mi-table text-sm">
                <thead>
                    <tr>
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
                        @php $isWh = $balance->location instanceof \App\Models\Warehouse; @endphp
                        <tr>
                            <td>
                                <span class="inv-loc-chip {{ $isWh ? 'inv-loc-chip-wh' : 'inv-loc-chip-sh' }}">
                                    <i class="fas fa-{{ $isWh ? 'warehouse' : 'store' }} text-[0.6rem]"></i>
                                    {{ $balance->location?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="font-medium">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                            <td>{{ number_format($balance->quantity_reserved, 0) }}</td>
                            <td>{{ number_format($balance->quantity_available, 0) }}</td>
                            <td>{{ number_format($balance->average_cost, 2) }}</td>
                            <td class="font-semibold text-orange-700">{{ number_format($balance->stockValue(), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-10 text-center text-gray-400">
                                No stock recorded at any location yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
