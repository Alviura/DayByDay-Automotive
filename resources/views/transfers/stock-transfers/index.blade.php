<x-app-layout title="Stock Transfers">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex items-start gap-3">
            <div class="mi-page-icon"><i class="fas fa-truck"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold">Stock Transfers</h1>
                <p class="text-sm text-gray-500">Dispatched movements awaiting receipt at destination.</p>
            </div>
        </div>
        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Transfer</th><th>Request</th><th>Route</th><th>Status</th><th>Dispatched</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td><a href="{{ route('stock-transfers.show', $transfer) }}" class="mi-cat-badge">{{ $transfer->transfer_number }}</a></td>
                                <td>{{ $transfer->transferRequest?->request_number ?? '—' }}</td>
                                <td class="text-sm">{{ $transfer->routeLabel() }}</td>
                                <td>{{ $transfer->statusLabel() }}</td>
                                <td class="text-sm text-gray-500">{{ $transfer->dispatched_at?->format('d M Y') ?? '—' }}</td>
                                <td><a href="{{ route('stock-transfers.show', $transfer) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12 text-gray-400">No stock transfers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transfers->hasPages())<div class="mi-card-foot">{{ $transfers->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
