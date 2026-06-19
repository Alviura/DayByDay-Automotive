<x-app-layout title="Inventory Valuation">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-coins"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Inventory Valuation</h1>
                    <p class="text-sm text-gray-500">Stock value by location — quantity on hand × weighted average cost.</p>
                </div>
            </div>
            <a href="{{ route('inventory.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Balances</a>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-orange">
                <div><p class="mi-kpi-label">Grand Total Value</p><p class="mi-kpi-value orange">{{ number_format($grandTotal, 2) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div><p class="mi-kpi-label">Total Units</p><p class="mi-kpi-value">{{ number_format($grandUnits, 0) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div><p class="mi-kpi-label">SKUs Held</p><p class="mi-kpi-value">{{ number_format($grandSkus) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Location</th><th>Type</th><th>SKUs</th><th>Units</th><th>Value (KES)</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($locations as $row)
                            <tr>
                                <td class="font-medium">{{ $row['location']->name }}</td>
                                <td><span class="mi-cat-badge">{{ $row['type'] }}</span></td>
                                <td>{{ number_format($row['sku_count']) }}</td>
                                <td>{{ number_format($row['total_units'], 0) }}</td>
                                <td class="font-semibold">{{ number_format($row['total_value'], 2) }}</td>
                                <td>
                                    <a href="{{ route('inventory.valuation', ['location_type' => strtolower($row['type']), 'location_id' => $row['location']->id]) }}"
                                       class="text-sm text-orange-600">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($detail)
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Location Detail</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Avg Cost</th><th>Value</th></tr></thead>
                        <tbody>
                            @foreach ($detail['balances'] as $balance)
                                <tr>
                                    <td>{{ $balance->product->part_number }} — {{ $balance->product->name }}</td>
                                    <td>{{ number_format($balance->quantity_on_hand, 2) }}</td>
                                    <td>{{ number_format($balance->average_cost, 2) }}</td>
                                    <td>{{ number_format($balance->stockValue(), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
