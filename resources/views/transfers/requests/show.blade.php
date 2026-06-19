<x-app-layout :title="$request->request_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold">{{ $request->request_number }}</h1>
                        <span class="mi-status-pending">{{ $request->statusLabel() }}</span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $request->routeLabel() }} · {{ $request->typeLabel() }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transfer-requests.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($request->canSubmit())
                    @can('transfers.request')
                        <form action="{{ route('transfer-requests.submit', $request) }}" method="POST" class="inline" onsubmit="return confirm('Submit for approval?');">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit</button>
                        </form>
                    @endcan
                @endif
                @if ($request->canDispatch())
                    @can('transfers.dispatch')
                        <form action="{{ route('transfer-requests.dispatch', $request) }}" method="POST" class="inline" onsubmit="return confirm('Dispatch stock from source?');">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-truck text-xs"></i> Dispatch</button>
                        </form>
                    @endcan
                @endif
                @if ($request->stockTransfer && $request->stockTransfer->canReceive())
                    @can('transfers.receive')
                        <a href="{{ route('stock-transfers.receive', $request->stockTransfer) }}" class="mi-btn-orange">
                            <i class="fas fa-truck-ramp-box text-xs"></i> Receive
                        </a>
                    @endcan
                @endif
                @if ($request->approval)
                    <a href="{{ route('approvals.show', $request->approval) }}" class="mi-btn-ghost">View Approval</a>
                @endif
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Requested</th>
                            <th>Approved</th>
                            @if ($request->stockTransfer)
                                <th>Dispatched</th>
                                <th>Received</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($request->items as $item)
                            @php $transferItem = $request->stockTransfer?->items->firstWhere('product_id', $item->product_id); @endphp
                            <tr>
                                <td>{{ $item->product->part_number }} — {{ $item->product->name }}</td>
                                <td>{{ number_format($item->requested_quantity, 2) }}</td>
                                <td>{{ $item->approved_quantity ? number_format($item->approved_quantity, 2) : '—' }}</td>
                                @if ($request->stockTransfer)
                                    <td>{{ $transferItem ? number_format($transferItem->dispatched_quantity, 2) : '—' }}</td>
                                    <td>{{ $transferItem ? number_format($transferItem->received_quantity, 2) : '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($request->stockTransfer)
            <div class="mi-card p-5">
                <p class="text-sm font-semibold mb-2">Stock Transfer</p>
                <a href="{{ route('stock-transfers.show', $request->stockTransfer) }}" class="mi-cat-badge">
                    {{ $request->stockTransfer->transfer_number }} — {{ $request->stockTransfer->statusLabel() }}
                </a>
            </div>
        @endif

        @if ($request->notes)
            <div class="mi-card p-5">
                <p class="mi-field-label">Notes</p>
                <p class="text-sm text-gray-700 mt-2">{{ $request->notes }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
