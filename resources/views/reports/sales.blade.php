<x-app-layout title="Sales Report">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-cash-register"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">Sales Report</h1>
                    <p class="text-sm text-gray-500">{{ $filters->from->format('d M Y') }} — {{ $filters->to->format('d M Y') }}</p>
                </div>
            </div>
            <a href="{{ route('reports.index') }}" class="mi-btn-ghost">All Reports</a>
        </div>

        <x-reports.filters :filters="$filters" :report-type="$reportType" :shops="$shops" :scoped-shop-id="$scopedShopId" />

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Transactions</p><p class="mi-kpi-value">{{ $summary['transaction_count'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Revenue</p><p class="mi-kpi-value">{{ number_format($summary['gross_revenue'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Avg Ticket</p><p class="mi-kpi-value">{{ number_format($summary['avg_ticket'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Tax Collected</p><p class="mi-kpi-value orange">{{ number_format($summary['tax_collected'] ?? 0, 0) }}</p></div></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Daily Revenue</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Date</th><th>Transactions</th><th>Revenue</th></tr></thead>
                        <tbody>
                            @forelse ($daily as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                                    <td>{{ $row->transactions }}</td>
                                    <td>{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-8 text-gray-400">No sales in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Top Products</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
                        <tbody>
                            @forelse ($topProducts as $row)
                                <tr>
                                    <td>{{ $row->part_number }}</td>
                                    <td>{{ number_format($row->qty_sold, 0) }}</td>
                                    <td>{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-8 text-gray-400">No data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
