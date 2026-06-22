{{-- GRN show page sidebar --}}
<aside class="mi-card grn-sidebar overflow-hidden">

    {{-- Warehouse highlight --}}
    <div class="grn-sidebar-hero">
        <div class="grn-sidebar-hero-icon"><i class="fas fa-warehouse"></i></div>
        <div class="min-w-0">
            <p class="grn-sidebar-hero-label">Received into</p>
            <p class="grn-sidebar-hero-title">{{ $goodsReceiptNote->warehouse?->name ?? '—' }}</p>
            @if ($goodsReceiptNote->warehouse?->code)
                <p class="grn-sidebar-hero-sub">{{ $goodsReceiptNote->warehouse->code }}</p>
            @endif
        </div>
    </div>

    {{-- Receipt meta --}}
    <div class="grn-sidebar-body">
        <div class="grn-sidebar-block">
            <p class="grn-sidebar-block-title"><i class="fas fa-clipboard-check"></i> Receipt</p>
            <dl class="grn-detail-list">
                <div class="grn-detail-row">
                    <dt>GRN</dt>
                    <dd class="font-semibold">{{ $goodsReceiptNote->grn_number }}</dd>
                </div>
                <div class="grn-detail-row">
                    <dt>Status</dt>
                    <dd>
                        @if ($goodsReceiptNote->isVoided())
                            <span class="grn-badge grn-badge-slate">Voided</span>
                        @else
                            <span class="grn-badge grn-badge-green">Posted</span>
                        @endif
                    </dd>
                </div>
                <div class="grn-detail-row">
                    <dt>Date</dt>
                    <dd>{{ $goodsReceiptNote->received_at?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="grn-detail-row">
                    <dt>Time</dt>
                    <dd>{{ $goodsReceiptNote->received_at?->format('H:i') ?? '—' }}</dd>
                </div>
                <div class="grn-detail-row">
                    <dt>By</dt>
                    <dd>{{ $goodsReceiptNote->receiver?->name ?? '—' }}</dd>
                </div>
                <div class="grn-detail-row">
                    <dt>Units</dt>
                    <dd><span class="grn-qty-good">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($receivedQty) }}</span> received</dd>
                </div>
            </dl>
        </div>

        @if ($goodsReceiptNote->notes)
            <div class="grn-sidebar-note">
                <p class="grn-sidebar-note-label"><i class="fas fa-note-sticky"></i> Notes</p>
                <p class="grn-sidebar-note-text">{{ $goodsReceiptNote->notes }}</p>
            </div>
        @endif

        @if ($goodsReceiptNote->purchaseOrder || $goodsReceiptNote->quotationSeries)
            <div class="grn-sidebar-block">
                <p class="grn-sidebar-block-title"><i class="fas fa-link"></i> Linked</p>
                <div class="grn-doc-stack">
                    @if ($goodsReceiptNote->purchaseOrder)
                        <a href="{{ route('purchase-orders.show', $goodsReceiptNote->purchaseOrder) }}" class="grn-doc-tile grn-doc-tile-blue">
                            <div class="grn-doc-tile-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="grn-doc-tile-body min-w-0">
                                <p class="grn-doc-tile-label">Purchase Order</p>
                                <p class="grn-doc-tile-title">{{ $goodsReceiptNote->purchaseOrder->po_number }}</p>
                                <p class="grn-doc-tile-sub">{{ $goodsReceiptNote->purchaseOrder->supplier?->name }}</p>
                            </div>
                            <i class="fas fa-chevron-right grn-doc-tile-arrow"></i>
                        </a>
                    @endif
                    @if ($goodsReceiptNote->quotationSeries)
                        <a href="{{ route('quotation-series.show', $goodsReceiptNote->quotationSeries) }}" class="grn-doc-tile grn-doc-tile-orange">
                            <div class="grn-doc-tile-icon"><i class="fas fa-folder-open"></i></div>
                            <div class="grn-doc-tile-body min-w-0">
                                <p class="grn-doc-tile-label">Quotation Series</p>
                                <p class="grn-doc-tile-title truncate">{{ $goodsReceiptNote->quotationSeries->displayName() }}</p>
                                <p class="grn-doc-tile-sub">{{ $goodsReceiptNote->quotationSeries->series_number }}</p>
                            </div>
                            <i class="fas fa-chevron-right grn-doc-tile-arrow"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</aside>
