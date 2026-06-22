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

    <div class="mi-page space-y-5" x-data="{ voidOpen: {{ $errors->has('reason') ? 'true' : 'false' }} }" x-init="if (voidOpen) $nextTick(() => $refs.voidPanel?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-truck-ramp-box"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $goodsReceiptNote->grn_number }}</h1>
                        @if ($goodsReceiptNote->isVoided())
                            <span class="grn-badge grn-badge-slate">Voided</span>
                        @else
                            <span class="grn-badge grn-badge-green">Posted to Inventory</span>
                        @endif
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
                @if ($goodsReceiptNote->canVoid())
                    @can('procurement.manage')
                        <button type="button" class="mi-btn-ghost text-rose-600 border-rose-200 hover:bg-rose-50" x-show="!voidOpen" @click="voidOpen = true; $nextTick(() => $refs.voidPanel?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">
                            <i class="fas fa-ban text-xs"></i> Void Receipt
                        </button>
                    @endcan
                @endif
            </div>
        </div>

        @include('goods-receipts.partials.void-panel', [
            'goodsReceiptNote' => $goodsReceiptNote,
            'lineCount' => $lineCount,
            'goodQty' => $goodQty,
            'damagedQty' => $damagedQty,
        ])

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
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-5">

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
            @include('goods-receipts.partials.show-sidebar', [
                'goodsReceiptNote' => $goodsReceiptNote,
                'receivedQty' => $receivedQty,
            ])

        </div>
    </div>
</x-app-layout>
