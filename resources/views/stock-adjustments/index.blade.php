<x-app-layout title="Stock Adjustments">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-sliders"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Stock Adjustments</h1>
                    <p class="text-sm text-gray-500">Count variances and corrections — require approval before ledger posting.</p>
                </div>
            </div>
            @can('inventory.adjust')
                <a href="{{ route('stock-adjustments.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> New Adjustment
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value">{{ $stats['total'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-list"></i></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Draft</p><p class="mi-kpi-value">{{ $stats['draft'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-pen"></i></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Pending</p><p class="mi-kpi-value orange">{{ $stats['pending'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Approved</p><p class="mi-kpi-value">{{ $stats['approved'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div></div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Location</th>
                            <th>Reason</th>
                            <th>Lines</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustments as $adjustment)
                            <tr>
                                <td class="text-gray-400">{{ $adjustments->firstItem() + $loop->index }}</td>
                                <td><a href="{{ route('stock-adjustments.show', $adjustment) }}" class="mi-cat-badge hover:bg-orange-50">{{ $adjustment->adjustment_number }}</a></td>
                                <td class="text-sm">{{ $adjustment->locationLabel() }}</td>
                                <td class="text-sm">{{ $adjustment->reasonLabel() }}</td>
                                <td>{{ $adjustment->items->count() }}</td>
                                <td><span class="mi-status-{{ $adjustment->status === 'approved' ? 'active' : ($adjustment->status === 'pending' ? 'pending' : ($adjustment->status === 'rejected' ? 'rejected' : 'inactive')) }}">{{ $adjustment->statusLabel() }}</span></td>
                                <td class="text-sm text-gray-500">{{ $adjustment->created_at->format('d M Y') }}</td>
                                <td><a href="{{ route('stock-adjustments.show', $adjustment) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="!py-14 text-center text-gray-400">No adjustments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($adjustments->hasPages())
                <div class="mi-card-foot">{{ $adjustments->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
