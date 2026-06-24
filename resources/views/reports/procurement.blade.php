<x-app-layout title="Quotation Series Report">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">Quotation Series Report</h1>
                    <p class="text-sm text-gray-500">{{ $filters->from->format('d M Y') }} — {{ $filters->to->format('d M Y') }}</p>
                </div>
            </div>
            <a href="{{ route('reports.index') }}" class="mi-btn-ghost">All Reports</a>
        </div>

        <x-reports.filters :filters="$filters" :report-type="$reportType" :definition="$definition ?? []" :shops="$shops" :warehouses="$warehouses ?? collect()" :suppliers="$suppliers ?? collect()" :scoped-shop-id="$scopedShopId" :scoped-warehouse-id="$scopedWarehouseId ?? null" />

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Open Series</p><p class="mi-kpi-value">{{ $summary['series_open'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">New in Period</p><p class="mi-kpi-value">{{ $summary['series_in_period'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">PO Value</p><p class="mi-kpi-value">{{ number_format($summary['po_value'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">GRNs</p><p class="mi-kpi-value orange">{{ $summary['grn_count'] }}</p></div></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Series by Status</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Status</th><th>Count</th><th>Value</th></tr></thead>
                        <tbody>
                            @forelse ($statusBreakdown as $row)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</td>
                                    <td>{{ $row->count }}</td>
                                    <td>{{ number_format($row->value ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-8 text-gray-400">No quotation series.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Recent Purchase Orders</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>PO</th><th>Supplier</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($recentPos as $po)
                                <tr>
                                    <td>{{ $po->po_number }}</td>
                                    <td>{{ $po->supplier?->name }}</td>
                                    <td>{{ number_format($po->total, 2) }}</td>
                                    <td>{{ $po->statusLabel() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-8 text-gray-400">No POs.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
