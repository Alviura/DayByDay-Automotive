<x-app-layout title="Transfer Report">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">Transfer Report</h1>
                    <p class="text-sm text-gray-500">{{ $filters->from->format('d M Y') }} — {{ $filters->to->format('d M Y') }}</p>
                </div>
            </div>
            <a href="{{ route('reports.index') }}" class="mi-btn-ghost">All Reports</a>
        </div>

        <x-reports.filters :filters="$filters" :report-type="$reportType" :definition="$definition ?? []" :shops="$shops" :warehouses="$warehouses ?? collect()" :suppliers="$suppliers ?? collect()" :scoped-shop-id="$scopedShopId" :scoped-warehouse-id="$scopedWarehouseId ?? null" />

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Requests</p><p class="mi-kpi-value">{{ $summary['requests_in_period'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Pending</p><p class="mi-kpi-value">{{ $summary['pending'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">In Transit</p><p class="mi-kpi-value orange">{{ $summary['in_transit'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Completed</p><p class="mi-kpi-value">{{ $summary['completed_in_period'] }}</p></div></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">By Status</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Status</th><th>Count</th></tr></thead>
                        <tbody>
                            @forelse ($statusBreakdown as $row)
                                <tr><td>{{ ucfirst($row->status) }}</td><td>{{ $row->count }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center py-8 text-gray-400">No requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Recent Requests</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Request</th><th>Route</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($recentRequests as $req)
                                <tr>
                                    <td>{{ $req->request_number }}</td>
                                    <td class="text-sm">{{ $req->routeLabel() }}</td>
                                    <td>{{ $req->statusLabel() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-8 text-gray-400">No requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
