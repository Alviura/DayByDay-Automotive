@props(['warehouse', 'transfers', 'returns', 'receipts', 'adjustments'])

<div class="space-y-5">

    @can('procurement.view')
        @if ($receipts->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center justify-between w-full gap-3">
                        <p class="text-sm font-semibold text-gray-800">Goods receipts (GRN)</p>
                        <a href="{{ route('goods-receipts.index', ['warehouse_id' => $warehouse->id]) }}" class="text-xs text-orange-600 font-semibold hover:underline">All receipts</a>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>GRN</th><th>Source</th><th>Lines</th><th>Status</th><th>Received</th></tr></thead>
                        <tbody>
                            @foreach ($receipts as $grn)
                                <tr class="rt-index-row" onclick="window.location='{{ route('goods-receipts.show', $grn) }}'">
                                    <td class="font-mono font-semibold">{{ $grn->grn_number }}</td>
                                    <td class="text-sm text-gray-600">
                                        {{ $grn->purchaseOrder?->po_number ?? $grn->quotationSeries?->series_number ?? '—' }}
                                    </td>
                                    <td>{{ $grn->items_count }}</td>
                                    <td>{{ $grn->statusLabel() }}</td>
                                    <td class="text-gray-500">{{ $grn->received_at?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endcan

    @can('transfers.view')
        @if ($transfers->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center justify-between w-full gap-3">
                        <p class="text-sm font-semibold text-gray-800">Stock transfers</p>
                        <a href="{{ route('stock-transfers.index') }}" class="text-xs text-orange-600 font-semibold hover:underline">All transfers</a>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>Request</th><th>Route</th><th>Lines</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($transfers as $transfer)
                                <tr class="rt-index-row" onclick="window.location='{{ route('transfer-requests.show', $transfer) }}'">
                                    <td class="font-mono font-semibold">{{ $transfer->request_number }}</td>
                                    <td class="text-sm text-gray-600">{{ $transfer->routeLabel() }}</td>
                                    <td>{{ $transfer->items_count }}</td>
                                    <td><span class="{{ $transfer->statusBadgeClass() }}">{{ $transfer->statusLabel() }}</span></td>
                                    <td class="text-gray-500">{{ $transfer->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endcan

    @can('returns.view')
        @if ($returns->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center justify-between w-full gap-3">
                        <p class="text-sm font-semibold text-gray-800">Supplier returns</p>
                        <a href="{{ route('supplier-returns.index') }}" class="text-xs text-orange-600 font-semibold hover:underline">All returns</a>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>Return</th><th>Supplier</th><th>Lines</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($returns as $return)
                                <tr class="rt-index-row" onclick="window.location='{{ route('supplier-returns.show', $return) }}'">
                                    <td class="font-mono font-semibold">{{ $return->return_number }}</td>
                                    <td>{{ $return->supplier?->name ?? '—' }}</td>
                                    <td>{{ $return->items_count }}</td>
                                    <td>@include('returns.partials.status-badge', ['return' => $return])</td>
                                    <td class="text-gray-500">{{ $return->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endcan

    @can('inventory.adjust')
        @if ($adjustments->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center justify-between w-full gap-3">
                        <p class="text-sm font-semibold text-gray-800">Stock adjustments</p>
                        <a href="{{ route('stock-adjustments.index') }}" class="text-xs text-orange-600 font-semibold hover:underline">All adjustments</a>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>Adjustment</th><th>Reason</th><th>Lines</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($adjustments as $adjustment)
                                <tr class="rt-index-row" onclick="window.location='{{ route('stock-adjustments.show', $adjustment) }}'">
                                    <td class="font-mono font-semibold">{{ $adjustment->adjustment_number }}</td>
                                    <td>{{ $adjustment->reasonLabel() }}</td>
                                    <td>{{ $adjustment->items_count }}</td>
                                    <td>{{ $adjustment->statusLabel() }}</td>
                                    <td class="text-gray-500">{{ $adjustment->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endcan

    @if ($receipts->isEmpty() && $transfers->isEmpty() && $returns->isEmpty() && $adjustments->isEmpty())
        <div class="mi-card">
            <div class="mi-show-empty">
                <i class="fas fa-warehouse"></i>
                <p>No procurement or transfer activity recorded for this warehouse yet.</p>
            </div>
        </div>
    @endif
</div>
