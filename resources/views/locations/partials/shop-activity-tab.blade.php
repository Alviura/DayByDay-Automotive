@props(['shop', 'stats', 'recentSales', 'heldSales', 'transfers', 'returns', 'adjustments'])

<div class="space-y-5">

    @can('sales.view')
        <div class="mi-kpi-row !grid-cols-2 lg:!grid-cols-4">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Today's Sales</p>
                    <p class="mi-kpi-value">{{ number_format($stats['completed_today']) }}</p>
                    <p class="inv-kpi-sub">KES {{ number_format($stats['today_total'], 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Avg Ticket</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['avg_ticket_today'], 2) }}</p>
                    <p class="inv-kpi-sub">Today completed</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">At Cash Desk</p>
                    <p class="mi-kpi-value">{{ number_format($stats['held']) }}</p>
                    <p class="inv-kpi-sub">Held orders waiting</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">All-Time Sales</p>
                    <p class="mi-kpi-value">{{ number_format($stats['completed_total']) }}</p>
                    <p class="inv-kpi-sub">Completed receipts</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>

        @if ($heldSales->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center justify-between w-full gap-3">
                        <p class="text-sm font-semibold text-gray-800">Held at cash desk</p>
                        @can('sales.create')
                            <a href="{{ route('sales.desk', ['shop_id' => $shop->id]) }}" class="text-xs text-orange-600 font-semibold hover:underline">Open desk</a>
                        @endcan
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>Receipt</th><th>Lines</th><th>Total</th><th>Waiting</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($heldSales as $sale)
                                <tr class="rt-index-row" onclick="window.location='{{ route('sales.show', $sale) }}'">
                                    <td class="font-mono font-semibold">{{ $sale->receipt_number }}</td>
                                    <td>{{ $sale->items_count }}</td>
                                    <td class="font-semibold">{{ number_format($sale->total, 2) }}</td>
                                    <td class="text-gray-500">{{ $sale->created_at->diffForHumans() }}</td>
                                    <td><i class="fas fa-chevron-right text-xs text-gray-300"></i></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center justify-between w-full gap-3">
                    <p class="text-sm font-semibold text-gray-800">Recent sales</p>
                    <a href="{{ route('sales.index', ['shop_id' => $shop->id]) }}" class="text-xs text-orange-600 font-semibold hover:underline">Sales history</a>
                </div>
            </div>
            @if ($recentSales->isNotEmpty())
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Customer</th>
                                <th>Lines</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentSales as $sale)
                                <tr class="rt-index-row" onclick="window.location='{{ route('sales.show', $sale) }}'">
                                    <td class="font-mono font-semibold">{{ $sale->receipt_number }}</td>
                                    <td class="text-gray-600">{{ $sale->customerAccount?->name ?? $sale->customer_name ?? 'Walk-in' }}</td>
                                    <td>{{ $sale->items_count }}</td>
                                    <td class="font-semibold text-orange-700">{{ number_format($sale->total, 2) }}</td>
                                    <td class="text-gray-500">{{ $sale->sold_at?->format('d M Y H:i') }}</td>
                                    <td><i class="fas fa-chevron-right text-xs text-gray-300"></i></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mi-show-empty"><i class="fas fa-receipt"></i><p>No completed sales at this shop yet.</p></div>
            @endif
        </div>
    @endcan

    @can('transfers.view')
        @if ($transfers->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center justify-between w-full gap-3">
                        <p class="text-sm font-semibold text-gray-800">Transfers</p>
                        <a href="{{ route('transfer-requests.index') }}" class="text-xs text-orange-600 font-semibold hover:underline">All requests</a>
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
                        <p class="text-sm font-semibold text-gray-800">Customer returns</p>
                        <a href="{{ route('customer-returns.index') }}" class="text-xs text-orange-600 font-semibold hover:underline">All returns</a>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>Return</th><th>Sale</th><th>Lines</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($returns as $return)
                                <tr class="rt-index-row" onclick="window.location='{{ route('customer-returns.show', $return) }}'">
                                    <td class="font-mono font-semibold">{{ $return->return_number }}</td>
                                    <td>{{ $return->sale?->receipt_number ?? '—' }}</td>
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
</div>
