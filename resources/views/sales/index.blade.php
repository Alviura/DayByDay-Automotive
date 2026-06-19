<x-app-layout title="Sales">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-receipt"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Sales</h1>
                    <p class="text-sm text-gray-500">Completed, held, and reversed transactions.</p>
                </div>
            </div>
            @can('sales.create')
                <a href="{{ route('sales.pos') }}" class="mi-btn-orange"><i class="fas fa-cash-register text-xs"></i> Open POS</a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value">{{ $stats['total'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-list"></i></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Completed</p><p class="mi-kpi-value">{{ $stats['completed'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-check"></i></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">On Hold</p><p class="mi-kpi-value">{{ $stats['held'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-pause"></i></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Today</p><p class="mi-kpi-value orange">{{ number_format($stats['today_total'], 0) }}</p></div><div class="mi-kpi-icon"><i class="fas fa-coins"></i></div></div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr><th>Receipt</th><th>Shop</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="mi-cat-badge">{{ $sale->receipt_number }}</a></td>
                                <td class="text-sm">{{ $sale->shop?->name }}</td>
                                <td class="text-sm">{{ $sale->customer_name ?? 'Walk-in' }}</td>
                                <td>{{ number_format($sale->total, 2) }}</td>
                                <td><span class="mi-status-pending">{{ $sale->statusLabel() }}</span></td>
                                <td class="text-sm text-gray-500">{{ ($sale->sold_at ?? $sale->created_at)->format('d M Y H:i') }}</td>
                                <td><a href="{{ route('sales.show', $sale) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-12 text-gray-400">No sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($sales->hasPages())<div class="mi-card-foot">{{ $sales->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
