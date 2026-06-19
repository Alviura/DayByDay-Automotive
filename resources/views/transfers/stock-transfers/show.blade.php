<x-app-layout :title="$transfer->transfer_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[1.35rem] font-bold">{{ $transfer->transfer_number }}</h1>
                <p class="text-sm text-gray-500">{{ $transfer->routeLabel() }} · {{ $transfer->statusLabel() }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('stock-transfers.index') }}" class="mi-btn-ghost">Back</a>
                @if ($transfer->transferRequest)
                    <a href="{{ route('transfer-requests.show', $transfer->transferRequest) }}" class="mi-btn-ghost">View Request</a>
                @endif
                @if ($transfer->canReceive())
                    @can('transfers.receive')
                        <a href="{{ route('stock-transfers.receive', $transfer) }}" class="mi-btn-orange">
                            <i class="fas fa-truck-ramp-box text-xs"></i> Receive Goods
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Product</th><th>Dispatched</th><th>Received</th><th>Damaged</th><th>Good</th><th>Remaining</th></tr></thead>
                    <tbody>
                        @foreach ($transfer->items as $item)
                            <tr>
                                <td>{{ $item->product->part_number }}</td>
                                <td>{{ number_format($item->dispatched_quantity, 2) }}</td>
                                <td>{{ number_format($item->received_quantity, 2) }}</td>
                                <td>{{ number_format($item->damaged_quantity, 2) }}</td>
                                <td class="text-green-600 font-medium">{{ number_format($item->goodQuantity(), 2) }}</td>
                                <td>{{ number_format($item->remainingQuantity(), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
