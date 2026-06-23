<div class="db-role-shell">
@include('dashboard.partials.command-strip', ['strip' => $commandStrip ?? ['cards' => [], 'summary' => []]])

<div class="db-layout db-layout--with-side">
    <div class="db-main">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            @if (($dispatchQueue ?? collect())->isNotEmpty())
                <div class="mi-card">
                    <div class="mi-card-head">
                        <h2 class="text-sm font-bold text-gray-900">Awaiting Dispatch</h2>
                        <a href="{{ route('stock-transfers.index', ['status' => 'approved']) }}" class="text-xs font-semibold text-orange-600 hover:underline">View all</a>
                    </div>
                    <div class="db-table-wrap">
                        <table class="db-table">
                            <thead>
                                <tr>
                                    <th>Transfer</th>
                                    <th>Destination</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dispatchQueue as $transfer)
                                    <tr>
                                        <td>
                                            <a href="{{ route('stock-transfers.show', $transfer) }}">{{ $transfer->transfer_number }}</a>
                                        </td>
                                        <td class="text-gray-500">{{ $transfer->destinationLabel() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @include('dashboard.partials.low-stock', ['items' => $lowStock ?? collect()])
        </div>
    </div>

    <div class="db-side">
        @include('dashboard.partials.quick-actions', ['actions' => $quickActions])

        @if ($location ?? null)
            <div class="mi-card p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Warehouse</p>
                <p class="font-semibold text-gray-900">{{ $location->name }}</p>
                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $location->code }}</p>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    @if (($recentReceipts ?? collect())->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <h2 class="text-sm font-bold text-gray-900">Recent Receipts</h2>
                <a href="{{ route('goods-receipts.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">View all</a>
            </div>
            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>GRN</th>
                            <th>PO / Series</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentReceipts as $grn)
                            <tr>
                                <td>
                                    <a href="{{ route('goods-receipts.show', $grn) }}">{{ $grn->grn_number }}</a>
                                </td>
                                <td class="text-gray-500 text-xs">
                                    {{ $grn->purchaseOrder?->po_number ?? $grn->quotationSeries?->series_number ?? '—' }}
                                </td>
                                <td class="text-gray-400">{{ $grn->received_at?->diffForHumans(short: true) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (($recentTransfers ?? collect())->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <h2 class="text-sm font-bold text-gray-900">Recent Transfers</h2>
                <a href="{{ route('stock-transfers.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">View all</a>
            </div>
            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Request / Transfer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransfers as $item)
                            <tr>
                                <td>
                                    @if ($item instanceof \App\Models\StockTransfer)
                                        <a href="{{ route('stock-transfers.show', $item) }}">{{ $item->transfer_number }}</a>
                                    @else
                                        <a href="{{ route('transfer-requests.show', $item) }}">{{ $item->request_number }}</a>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-xs font-semibold text-gray-500">{{ $item->statusLabel() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
</div>
