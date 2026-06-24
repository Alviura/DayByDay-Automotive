@props(['supplier', 'purchaseOrders', 'poPipeline', 'stats'])

<div class="space-y-5">
    <div class="mi-kpi-row !grid-cols-2 lg:!grid-cols-4">
        <div class="mi-kpi mi-kpi-purple">
            <div>
                <p class="mi-kpi-label">Total POs</p>
                <p class="mi-kpi-value">{{ number_format($stats['purchase_orders_total']) }}</p>
                <p class="sp-kpi-sub">{{ number_format($stats['purchase_orders_open']) }} awaiting receipt</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-file-invoice"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-indigo" style="--kpi-accent:#6366f1">
            <div>
                <p class="mi-kpi-label">Lifetime Spend</p>
                <p class="mi-kpi-value" style="font-size:1rem">{{ number_format($stats['lifetime_spend'], 0) }}</p>
                <p class="sp-kpi-sub">{{ $supplier->currency }} all time</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-amber">
            <div>
                <p class="mi-kpi-label">Open Value</p>
                <p class="mi-kpi-value">{{ number_format($stats['open_po_value'], 0) }}</p>
                <p class="sp-kpi-sub">Outstanding on open POs</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-green">
            <div>
                <p class="mi-kpi-label">Received</p>
                <p class="mi-kpi-value">{{ number_format($stats['received_value'], 0) }}</p>
                <p class="sp-kpi-sub">Stock received to date</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-truck-ramp-box"></i></div>
        </div>
    </div>

    <div class="mi-card p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">PO status breakdown</p>
        <div class="sp-pipeline">
            @foreach ($poPipeline as $step)
                <div class="sp-pipe-step">
                    <div class="sp-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                    <span class="sp-pipe-count">{{ $step['count'] }}</span>
                    <span class="sp-pipe-label">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mi-card">
        <div class="mi-card-head">
            <div class="flex items-center justify-between w-full gap-3">
                <p class="text-sm font-semibold text-gray-800">Purchase orders</p>
                @can('procurement.view')
                    <a href="{{ route('purchase-orders.index', ['supplier_id' => $supplier->id]) }}" class="text-xs text-orange-600 font-semibold hover:underline">View all</a>
                @endcan
            </div>
        </div>
        @if ($purchaseOrders->isNotEmpty())
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Series</th>
                            <th>Lines</th>
                            <th>Order date</th>
                            <th>Total</th>
                            <th>Delivery</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrders as $order)
                            <tr class="sp-index-row" onclick="window.location='{{ route('purchase-orders.show', $order) }}'">
                                <td class="font-mono font-semibold">{{ $order->po_number }}</td>
                                <td class="text-xs text-gray-500 font-mono">{{ $order->quotationSeries?->series_number ?? '—' }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td class="text-gray-500">{{ $order->order_date?->format('d M Y') ?? '—' }}</td>
                                <td class="font-semibold text-indigo-700">{{ number_format($order->total, 2) }}</td>
                                <td>@include('purchase-orders.partials.delivery-badge', ['order' => $order])</td>
                                <td>@include('purchase-orders.partials.status-badge', ['order' => $order])</td>
                                <td><i class="fas fa-chevron-right text-xs text-gray-300"></i></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mi-show-empty">
                <i class="fas fa-file-invoice"></i>
                <p>No purchase orders issued to this supplier yet.</p>
            </div>
        @endif
    </div>
</div>
