@php
    $lineCount = $goodsReceiptNote->items->count();
    $receivedQty = $goodsReceiptNote->totalReceivedQuantity();
    $damagedQty = $goodsReceiptNote->totalDamagedQuantity();
    $goodQty = $goodsReceiptNote->totalGoodQuantity();
    $goodRate = $receivedQty > 0 ? round($goodQty / $receivedQty * 100) : 100;
@endphp

<x-app-layout :title="$goodsReceiptNote->grn_number">
    @push('styles')
        <x-module.page-index-styles />
        @include('goods-receipts.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-truck-ramp-box"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $goodsReceiptNote->grn_number }}</h1>
                        <span class="grn-badge grn-badge-green">Posted to Inventory</span>
                        @if ($goodsReceiptNote->hasDamage())
                            <span class="grn-badge grn-badge-amber">Has Damage</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span><i class="fas fa-warehouse text-[0.65rem]"></i> {{ $goodsReceiptNote->warehouse?->name }}</span>
                        <span class="mi-cat-badge">{{ $goodsReceiptNote->received_at?->format('d M Y H:i') }}</span>
                        @if ($goodsReceiptNote->receiver)
                            <span>by {{ $goodsReceiptNote->receiver->name }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('goods-receipts.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> All Receipts</a>
                @if ($goodsReceiptNote->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $goodsReceiptNote->purchaseOrder) }}" class="mi-btn-ghost">
                        <i class="fas fa-file-invoice-dollar text-xs"></i> View PO
                    </a>
                @endif
            </div>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Line Items</p>
                    <p class="mi-kpi-value">{{ $lineCount }}</p>
                    <p class="grn-kpi-sub">Products in this receipt</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Good Stock</p>
                    <p class="mi-kpi-value">{{ number_format($goodQty, 0) }}</p>
                    <p class="grn-kpi-sub">{{ $goodRate }}% of received units</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-box-open"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Damaged</p>
                    <p class="mi-kpi-value">{{ number_format($damagedQty, 0) }}</p>
                    <p class="grn-kpi-sub">Excluded from inventory</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Inventory Value</p>
                    <p class="mi-kpi-value orange" style="font-size:1.15rem">{{ number_format($goodsReceiptNote->totalValue(), 2) }}</p>
                    <p class="grn-kpi-sub">Good qty × unit cost</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>

        {{-- Split layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5">

            {{-- Line items --}}
            <div class="mi-card">
                <div class="mi-card-head">
                    <div>
                        <p class="grn-section-title"><i class="fas fa-boxes-stacked"></i> Received Items</p>
                        <p class="grn-section-sub">Expected vs received quantities and condition</p>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Expected</th>
                                <th>Received</th>
                                <th>Damaged</th>
                                <th>Good Qty</th>
                                <th>Unit Cost</th>
                                <th>Line Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($goodsReceiptNote->items as $item)
                                <tr>
                                    <td>
                                        <span class="text-sm font-medium text-gray-800">{{ $item->product->part_number }}</span>
                                        <p class="mi-pkg-sub">{{ $item->product->name }}</p>
                                    </td>
                                    <td>{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($item->expected_quantity) }}</td>
                                    <td>{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($item->received_quantity) }}</td>
                                    <td>
                                        @if ($item->damaged_quantity > 0)
                                            <span class="grn-qty-damaged">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($item->damaged_quantity) }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td><span class="grn-qty-good">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($item->goodQuantity()) }}</span></td>
                                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                                    <td><span class="grn-qty-good">{{ number_format($item->goodQuantity() * $item->unit_cost, 2) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right font-semibold text-gray-600">Totals</td>
                                <td><span class="grn-qty-good">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($goodQty) }}</span></td>
                                <td></td>
                                <td><span class="grn-qty-good">{{ number_format($goodsReceiptNote->totalValue(), 2) }}</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">

                {{-- Receipt details --}}
                <div class="mi-card p-5 space-y-3">
                    <p class="grn-section-title"><i class="fas fa-circle-info"></i> Receipt Details</p>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">GRN Number</dt>
                            <dd class="font-semibold text-gray-800">{{ $goodsReceiptNote->grn_number }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Warehouse</dt>
                            <dd class="font-medium text-gray-800 text-right">{{ $goodsReceiptNote->warehouse?->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Received At</dt>
                            <dd class="text-gray-800">{{ $goodsReceiptNote->received_at?->format('d M Y H:i') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Received By</dt>
                            <dd class="text-gray-800">{{ $goodsReceiptNote->receiver?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Total Received</dt>
                            <dd class="text-gray-800">{{ number_format($receivedQty, 2) }} units</dd>
                        </div>
                    </dl>
                    @if ($goodsReceiptNote->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Notes</p>
                            <p class="text-sm text-gray-600">{{ $goodsReceiptNote->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- PO link --}}
                @if ($goodsReceiptNote->purchaseOrder)
                    <div class="mi-card p-5">
                        <p class="grn-section-title mb-3"><i class="fas fa-file-invoice-dollar"></i> Purchase Order</p>
                        <a href="{{ route('purchase-orders.show', $goodsReceiptNote->purchaseOrder) }}" class="grn-link-card">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="grn-link-card-icon" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $goodsReceiptNote->purchaseOrder->po_number }}</p>
                                    <p class="text-xs text-gray-400">{{ $goodsReceiptNote->purchaseOrder->supplier?->name }}</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        </a>
                    </div>
                @endif

                {{-- Quotation series link --}}
                @if ($goodsReceiptNote->quotationSeries)
                    <div class="mi-card p-5">
                        <p class="grn-section-title mb-3"><i class="fas fa-folder-open"></i> Quotation Series</p>
                        <a href="{{ route('quotation-series.show', $goodsReceiptNote->quotationSeries) }}" class="grn-link-card">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="grn-link-card-icon" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $goodsReceiptNote->quotationSeries->displayName() }}</p>
                                    <p class="text-xs text-gray-400">{{ $goodsReceiptNote->quotationSeries->series_number }}</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
