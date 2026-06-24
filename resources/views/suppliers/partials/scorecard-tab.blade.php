@props(['supplier', 'stats', 'monthlySpend', 'topProducts', 'openPurchaseOrders'])

<div class="space-y-5">

    @if ($stats['purchase_orders_total'] > 0)
        <div class="sp-score-ring">
            <div class="sp-score-icon"><i class="fas fa-handshake"></i></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-indigo-900">Procurement relationship</p>
                <p class="text-xs text-indigo-700/80 mt-0.5">
                    @if ($stats['first_order_date'])
                        Partner since {{ \Carbon\Carbon::parse($stats['first_order_date'])->format('M Y') }}
                    @endif
                    @if ($stats['last_order_date'])
                        · Last order {{ \Carbon\Carbon::parse($stats['last_order_date'])->diffForHumans() }}
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ number_format($stats['purchase_orders_total']) }} purchase orders ·
                    {{ number_format($stats['quotation_series_total']) }} quotation series ·
                    {{ number_format($stats['grn_count']) }} goods receipts
                </p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Lifetime spend</p>
                <p class="text-lg font-bold text-indigo-700">{{ $supplier->currency }} {{ number_format($stats['lifetime_spend'], 0) }}</p>
            </div>
        </div>
    @endif

    @if ($monthlySpend->isNotEmpty())
        @php $maxSpend = max(1, (float) $monthlySpend->max('total')); @endphp
        <div class="mi-card p-5">
            <p class="text-sm font-semibold text-gray-800 mb-1"><i class="fas fa-chart-column text-indigo-400 text-xs"></i> Order value — last 6 months</p>
            <p class="text-xs text-gray-400 mb-4">Purchase order totals by month</p>
            <div class="sp-spend-chart">
                @foreach ($monthlySpend as $row)
                    @php
                        $height = max(4, round((float) $row->total / $maxSpend * 100));
                        $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('M');
                    @endphp
                    <div class="sp-spend-bar-wrap" title="{{ $monthLabel }}: {{ number_format($row->total, 0) }}">
                        <div class="sp-spend-bar" style="height: {{ $height }}%"></div>
                        <span class="sp-spend-value">{{ number_format($row->total / 1000, 0) }}k</span>
                        <span class="sp-spend-label">{{ $monthLabel }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($openPurchaseOrders->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center justify-between w-full gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Awaiting delivery</p>
                        <p class="text-xs text-gray-400 mt-0.5">KES {{ number_format($stats['open_po_value'], 0) }} still expected</p>
                    </div>
                    @can('procurement.view')
                        <a href="{{ route('purchase-orders.index', ['supplier_id' => $supplier->id]) }}" class="text-xs text-orange-600 font-semibold hover:underline">All POs</a>
                    @endcan
                </div>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead><tr><th>PO</th><th>Expected</th><th>Progress</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($openPurchaseOrders as $po)
                            <tr class="sp-index-row" onclick="window.location='{{ route('purchase-orders.show', $po) }}'">
                                <td class="font-mono font-semibold">{{ $po->po_number }}</td>
                                <td class="text-gray-500">{{ $po->expected_date?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden max-w-[5rem]">
                                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $po->receiptProgressPercent() }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $po->receiptProgressPercent() }}%</span>
                                    </div>
                                </td>
                                <td class="font-semibold">{{ number_format($po->total, 2) }}</td>
                                <td>@include('purchase-orders.partials.status-badge', ['order' => $po])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($topProducts->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm font-semibold text-gray-800">Top products ordered</p>
                <p class="text-xs text-gray-400 mt-0.5">By lifetime PO line value</p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead><tr><th>Product</th><th>Qty ordered</th><th>Value</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($topProducts as $row)
                            <tr>
                                <td>
                                    <p class="font-medium">{{ $row->product?->part_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $row->product?->name }}</p>
                                </td>
                                <td class="font-semibold">{{ number_format($row->total_qty, 0) }}</td>
                                <td class="font-semibold text-indigo-700">{{ number_format($row->total_value, 2) }}</td>
                                <td>
                                    @can('products.view')
                                        @if ($row->product)
                                            <a href="{{ route('products.show', $row->product) }}" class="mi-action view"><i class="fas fa-eye"></i></a>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
