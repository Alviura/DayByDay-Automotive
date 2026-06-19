<x-app-layout title="Financial Report">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-coins"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">Financial Summary</h1>
                    <p class="text-sm text-gray-500">{{ $filters->from->format('d M Y') }} — {{ $filters->to->format('d M Y') }}</p>
                </div>
            </div>
            <a href="{{ route('reports.index') }}" class="mi-btn-ghost">All Reports</a>
        </div>

        <x-reports.filters :filters="$filters" :report-type="$reportType" :shops="$shops" :scoped-shop-id="$scopedShopId" />

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Gross Revenue</p><p class="mi-kpi-value">{{ number_format($summary['gross_revenue'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Refunds</p><p class="mi-kpi-value">{{ number_format($summary['refunds'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Net Revenue</p><p class="mi-kpi-value orange">{{ number_format($summary['net_revenue'], 0) }}</p></div></div>
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Inventory Value</p><p class="mi-kpi-value">{{ number_format($summary['inventory_value'], 0) }}</p></div></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="mi-card p-5">
                <p class="text-sm font-semibold mb-4">Period Summary</p>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Transactions</dt><dd>{{ $summary['transaction_count'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Discounts</dt><dd>{{ number_format($summary['discounts'], 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tax Collected</dt><dd>{{ number_format($summary['tax_collected'], 2) }}</dd></div>
                    <div class="flex justify-between font-semibold border-t pt-2"><dt>Net Revenue</dt><dd class="text-orange-600">{{ number_format($summary['net_revenue'], 2) }}</dd></div>
                </dl>
            </div>
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Payments by Method</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Method</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($paymentBreakdown as $row)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $row->method)) }}</td>
                                    <td>{{ number_format($row->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center py-8 text-gray-400">No payments.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
