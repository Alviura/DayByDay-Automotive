<x-app-layout title="Customer Returns">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-rotate-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">Customer Returns</h1>
                    <p class="text-sm text-gray-500">Returns linked to completed sales — restock good items after approval.</p>
                </div>
            </div>
            @can('returns.create')
                <a href="{{ route('customer-returns.create') }}" class="mi-btn-orange"><i class="fas fa-plus text-xs"></i> New Return</a>
            @endcan
        </div>
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value">{{ $stats['total'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Pending</p><p class="mi-kpi-value">{{ $stats['pending'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Completed</p><p class="mi-kpi-value">{{ $stats['completed'] }}</p></div></div>
        </div>
        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Return #</th><th>Sale</th><th>Shop</th><th>Refund</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($returns as $return)
                            <tr>
                                <td><a href="{{ route('customer-returns.show', $return) }}" class="mi-cat-badge">{{ $return->return_number }}</a></td>
                                <td>{{ $return->sale?->receipt_number ?? '—' }}</td>
                                <td>{{ $return->shop?->name }}</td>
                                <td>{{ number_format($return->refund_amount, 2) }}</td>
                                <td>{{ $return->statusLabel() }}</td>
                                <td><a href="{{ route('customer-returns.show', $return) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12 text-gray-400">No customer returns yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($returns->hasPages())<div class="mi-card-foot">{{ $returns->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
