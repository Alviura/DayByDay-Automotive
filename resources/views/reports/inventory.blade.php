<x-app-layout title="Inventory Report">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-boxes-stacked"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">Inventory Report</h1>
                    <p class="text-sm text-gray-500">Valuation and stock health</p>
                </div>
            </div>
            <a href="{{ route('reports.index') }}" class="mi-btn-ghost">All Reports</a>
        </div>

        <x-reports.filters :filters="$filters" :report-type="$reportType" :shops="$shops" :scoped-shop-id="$scopedShopId" />

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Total Value</p><p class="mi-kpi-value">{{ number_format($summary['total_value'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Units on Hand</p><p class="mi-kpi-value">{{ number_format($summary['total_units'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Low Stock SKUs</p><p class="mi-kpi-value orange">{{ $summary['low_stock_count'] }}</p></div></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Valuation by Location</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Location</th><th>SKUs</th><th>Units</th><th>Value</th></tr></thead>
                        <tbody>
                            @foreach ($locations as $loc)
                                <tr>
                                    <td>{{ $loc['type'] }}: {{ $loc['name'] }}</td>
                                    <td>{{ $loc['sku_count'] }}</td>
                                    <td>{{ number_format($loc['total_units'], 0) }}</td>
                                    <td>{{ number_format($loc['total_value'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Movements (period)</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Type</th><th>Entries</th><th>Qty</th></tr></thead>
                        <tbody>
                            @forelse ($movements as $row)
                                <tr>
                                    <td>{{ str_replace('_', ' ', ucfirst($row->transaction_type)) }}</td>
                                    <td>{{ $row->entries }}</td>
                                    <td>{{ number_format($row->total_qty, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-8 text-gray-400">No movements.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($lowStock->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Below Reorder Level</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Product</th><th>On Hand</th><th>Reorder</th></tr></thead>
                        <tbody>
                            @foreach ($lowStock as $balance)
                                <tr>
                                    <td>{{ $balance->product->part_number }}</td>
                                    <td class="text-orange-600 font-medium">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                                    <td>{{ $balance->product->reorder_level }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
