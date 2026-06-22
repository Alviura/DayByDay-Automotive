@php
    $openPoLines = $openPoLines ?? collect();
@endphp

<div class="space-y-5">
    @if ($incoming['units'] > 0)
        <div class="inv-phase-banner inv-phase-banner-blue">
            <i class="fas fa-truck"></i>
            <div>
                <strong>{{ number_format($incoming['units'], 0) }} units on order</strong>
                across {{ $incoming['lines'] }} open purchase order {{ str('line')->plural($incoming['lines']) }}.
            </div>
        </div>
    @endif

    @if ($openPoLines->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <div>
                    <p class="inv-section-title"><i class="fas fa-hourglass-half"></i> On Order</p>
                    <p class="inv-section-sub">Open PO lines awaiting receipt</p>
                </div>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Supplier</th>
                            <th>Ordered</th>
                            <th>Received</th>
                            <th>Remaining</th>
                            <th>Unit Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($openPoLines as $line)
                            @php $po = $line->purchaseOrder; @endphp
                            <tr>
                                <td>
                                    @can('procurement.view')
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="inv-ref-link">{{ $po->po_number }}</a>
                                    @else
                                        {{ $po->po_number }}
                                    @endcan
                                </td>
                                <td>{{ $po->supplier?->name ?? '—' }}</td>
                                <td>{{ number_format($line->quantity, 0) }}</td>
                                <td>{{ number_format($line->received_quantity, 0) }}</td>
                                <td class="font-semibold text-amber-700">{{ number_format($line->remainingQuantity(), 0) }}</td>
                                <td>{{ number_format($line->unit_cost, 2) }}</td>
                                <td>@include('purchase-orders.partials.status-badge', ['order' => $po])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mi-card">
        <div class="mi-card-head">
            <div>
                <p class="inv-section-title"><i class="fas fa-file-invoice-dollar"></i> Purchase Orders</p>
                <p class="inv-section-sub">PO lines that include this product</p>
            </div>
        </div>
        <div class="mi-table-wrap">
            <table class="mi-table text-sm">
                <thead>
                    <tr>
                        <th>PO</th>
                        <th>Supplier</th>
                        <th>Series</th>
                        <th>Ordered</th>
                        <th>Received</th>
                        <th>Unit Cost</th>
                        <th>Line Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($poLines as $line)
                        @php $po = $line->purchaseOrder; @endphp
                        <tr>
                            <td>
                                @can('procurement.view')
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="inv-ref-link">{{ $po->po_number }}</a>
                                @else
                                    {{ $po->po_number }}
                                @endcan
                            </td>
                            <td>{{ $po->supplier?->name ?? '—' }}</td>
                            <td class="text-xs">
                                @if ($po->quotationSeries)
                                    @can('procurement.view')
                                        <a href="{{ route('quotation-series.show', $po->quotationSeries) }}" class="text-orange-600 hover:underline">
                                            {{ $po->quotationSeries->series_number }}
                                        </a>
                                    @else
                                        {{ $po->quotationSeries->series_number }}
                                    @endcan
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>{{ number_format($line->quantity, 0) }}</td>
                            <td>{{ number_format($line->received_quantity, 0) }}</td>
                            <td>{{ number_format($line->unit_cost, 2) }}</td>
                            <td class="font-medium">{{ number_format($line->line_total, 2) }}</td>
                            <td>@include('purchase-orders.partials.status-badge', ['order' => $po])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!py-10 text-center text-gray-400">
                                This product has not been ordered on any purchase order yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mi-card">
        <div class="mi-card-head">
            <div>
                <p class="inv-section-title"><i class="fas fa-truck-ramp-box"></i> Goods Receipts</p>
                <p class="inv-section-sub">GRN lines that posted or voided stock for this product</p>
            </div>
        </div>
        <div class="mi-table-wrap">
            <table class="mi-table text-sm">
                <thead>
                    <tr>
                        <th>GRN</th>
                        <th>PO</th>
                        <th>Warehouse</th>
                        <th>Received</th>
                        <th>Damaged</th>
                        <th>Good Qty</th>
                        <th>Unit Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentReceipts as $line)
                        @php $grn = $line->goodsReceiptNote; @endphp
                        <tr @class(['opacity-60' => $grn?->isVoided()])>
                            <td>
                                @if ($grn)
                                    @can('procurement.view')
                                        <a href="{{ route('goods-receipts.show', $grn) }}" @class(['inv-ref-link', 'line-through' => $grn->isVoided()])>{{ $grn->grn_number }}</a>
                                    @else
                                        {{ $grn->grn_number }}
                                    @endcan
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-sm">
                                @if ($grn?->purchaseOrder)
                                    @can('procurement.view')
                                        <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}" class="text-orange-600 hover:underline">{{ $grn->purchaseOrder->po_number }}</a>
                                    @else
                                        {{ $grn->purchaseOrder->po_number }}
                                    @endcan
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $grn?->warehouse?->name ?? '—' }}</td>
                            <td>{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($line->received_quantity) }}</td>
                            <td>
                                @if ($line->damaged_quantity > 0)
                                    <span class="inv-qty-low">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($line->damaged_quantity) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="inv-qty-in">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($line->goodQuantity()) }}</td>
                            <td>{{ number_format($line->unit_cost, 2) }}</td>
                            <td>
                                @if ($grn?->isVoided())
                                    <span class="inv-badge inv-badge-rose">Voided</span>
                                @else
                                    <span class="inv-badge inv-badge-green">Posted</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!py-10 text-center text-gray-400">
                                No goods receipts recorded for this product yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($quotationItems->isNotEmpty())
        <div class="mi-card">
            <div class="mi-card-head">
                <div>
                    <p class="inv-section-title"><i class="fas fa-folder-open"></i> Quotation Series</p>
                    <p class="inv-section-sub">Import / procurement series that included this product</p>
                </div>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead>
                        <tr>
                            <th>Series</th>
                            <th>Supplier</th>
                            <th>Qty</th>
                            <th>Landed Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotationItems as $item)
                            @php $series = $item->series; @endphp
                            <tr>
                                <td>
                                    @if ($series)
                                        @can('procurement.view')
                                            <a href="{{ route('quotation-series.show', $series) }}" class="inv-ref-link">{{ $series->series_number }}</a>
                                        @else
                                            {{ $series->series_number }}
                                        @endcan
                                        @if ($series->title)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($series->title, 40) }}</p>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $series?->supplier?->name ?? '—' }}</td>
                                <td>{{ number_format($item->quantity, 0) }}</td>
                                <td>{{ number_format($item->landedUnitCost(), 2) }}</td>
                                <td>
                                    @if ($series)
                                        @include('quotation-series.partials.status-badge', ['series' => $series])
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
