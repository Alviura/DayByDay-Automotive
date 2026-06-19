<x-app-layout title="Transfer Requests">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Transfer Requests</h1>
                    <p class="text-sm text-gray-500">Warehouse→shop and inter-shop stock movements with approval.</p>
                </div>
            </div>
            @can('transfers.request')
                <a href="{{ route('transfer-requests.create') }}" class="mi-btn-orange"><i class="fas fa-plus text-xs"></i> New Request</a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value">{{ $stats['total'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-list"></i></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Draft</p><p class="mi-kpi-value">{{ $stats['draft'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-pen"></i></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Pending</p><p class="mi-kpi-value orange">{{ $stats['pending'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">In Transit</p><p class="mi-kpi-value">{{ $stats['in_transit'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-truck"></i></div></div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr><th>Request</th><th>Route</th><th>Type</th><th>Lines</th><th>Status</th><th>Requested</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $transferRequest)
                            <tr>
                                <td><a href="{{ route('transfer-requests.show', $transferRequest) }}" class="mi-cat-badge">{{ $transferRequest->request_number }}</a></td>
                                <td class="text-sm">{{ $transferRequest->routeLabel() }}</td>
                                <td class="text-sm">{{ $transferRequest->typeLabel() }}</td>
                                <td>{{ $transferRequest->items_count }}</td>
                                <td><span class="mi-status-pending">{{ $transferRequest->statusLabel() }}</span></td>
                                <td class="text-sm text-gray-500">{{ $transferRequest->created_at->format('d M Y') }}</td>
                                <td><a href="{{ route('transfer-requests.show', $transferRequest) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-12 text-gray-400">No transfer requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($requests->hasPages())<div class="mi-card-foot">{{ $requests->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
