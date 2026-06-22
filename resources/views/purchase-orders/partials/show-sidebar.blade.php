{{-- PO show page sidebar --}}
<aside class="mi-card po-sidebar overflow-hidden">

    {{-- Supplier hero --}}
    <div class="po-sidebar-hero">
        <div class="po-sidebar-hero-icon"><i class="fas fa-building"></i></div>
        <div class="min-w-0 flex-1">
            <p class="po-sidebar-hero-label">Supplier</p>
            <p class="po-sidebar-hero-title">{{ $purchaseOrder->supplier?->name ?? '—' }}</p>
            <p class="po-sidebar-hero-sub">{{ $purchaseOrder->po_number }} · {{ $purchaseOrder->currency }}</p>
        </div>
        <div class="po-sidebar-hero-stat">
            <span class="po-sidebar-hero-stat-val">{{ $receiptPct }}%</span>
            <span class="po-sidebar-hero-stat-lbl">Received</span>
        </div>
    </div>

    <div class="po-sidebar-body">

        {{-- Order details --}}
        <div class="po-sidebar-block">
            <p class="po-sidebar-block-title"><i class="fas fa-file-invoice-dollar"></i> Order</p>
            <dl class="po-detail-list">
                <div class="po-detail-row">
                    <dt>Status</dt>
                    <dd class="flex flex-wrap gap-1">
                        @include('purchase-orders.partials.status-badge', ['order' => $purchaseOrder])
                    </dd>
                </div>
                <div class="po-detail-row">
                    <dt>Delivery</dt>
                    <dd>@include('purchase-orders.partials.delivery-badge', ['order' => $purchaseOrder])</dd>
                </div>
                <div class="po-detail-row">
                    <dt>Ordered</dt>
                    <dd>{{ $purchaseOrder->order_date?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="po-detail-row">
                    <dt>Expected</dt>
                    <dd>{{ $purchaseOrder->expected_date?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="po-detail-row">
                    <dt>Total</dt>
                    <dd><span class="po-cost">{{ number_format($purchaseOrder->total, 2) }}</span> {{ $purchaseOrder->currency }}</dd>
                </div>
                <div class="po-detail-row">
                    <dt>Created</dt>
                    <dd>{{ $purchaseOrder->creator?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        @if ($purchaseOrder->notes)
            <div class="po-sidebar-note">
                <p class="po-sidebar-note-label"><i class="fas fa-note-sticky"></i> Notes</p>
                <p class="po-sidebar-note-text">{{ $purchaseOrder->notes }}</p>
            </div>
        @endif

        @if ($purchaseOrder->quotationSeries)
            <div class="po-sidebar-block">
                <p class="po-sidebar-block-title"><i class="fas fa-link"></i> Source</p>
                <a href="{{ route('quotation-series.show', $purchaseOrder->quotationSeries) }}" class="po-doc-tile po-doc-tile-orange">
                    <div class="po-doc-tile-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="po-doc-tile-body min-w-0">
                        <p class="po-doc-tile-label">Quotation Series</p>
                        <p class="po-doc-tile-title truncate">{{ $purchaseOrder->quotationSeries->displayName() }}</p>
                        <p class="po-doc-tile-sub">{{ $purchaseOrder->quotationSeries->series_number }}</p>
                    </div>
                    <i class="fas fa-chevron-right po-doc-tile-arrow"></i>
                </a>
            </div>
        @endif

        {{-- Goods receipts --}}
        @php
            $hasVoidedReceipt = $purchaseOrder->goodsReceiptNotes->contains(fn ($g) => $g->isVoided());
        @endphp
        <div class="po-sidebar-block">
            <div class="flex items-center justify-between gap-2 mb-2">
                <p class="po-sidebar-block-title mb-0"><i class="fas fa-truck-ramp-box"></i> Receipts</p>
                @if ($purchaseOrder->canReceive())
                    @can('procurement.manage')
                        <a href="{{ route('goods-receipts.create', $purchaseOrder) }}" class="po-receipt-new-btn">
                            <i class="fas fa-plus text-[0.55rem]"></i> New Receipt
                        </a>
                    @endcan
                @endif
            </div>

            @if ($hasVoidedReceipt && $purchaseOrder->canReceive())
                <div class="po-receipt-hint">
                    <i class="fas fa-circle-info"></i>
                    <p>A previous receipt was voided and reversed from stock. You can post a new GRN for the remaining quantities on this PO.</p>
                </div>
            @endif

            @if ($purchaseOrder->goodsReceiptNotes->isNotEmpty())
                <div class="po-doc-stack">
                    @foreach ($purchaseOrder->goodsReceiptNotes as $grn)
                        <a href="{{ route('goods-receipts.show', $grn) }}"
                           class="po-doc-tile po-doc-tile-emerald {{ $grn->isVoided() ? 'po-doc-tile-muted' : '' }}">
                            <div class="po-doc-tile-icon"><i class="fas fa-clipboard-check"></i></div>
                            <div class="po-doc-tile-body min-w-0">
                                <p class="po-doc-tile-label">GRN @if ($grn->isVoided()) · Voided @endif</p>
                                <p class="po-doc-tile-title">{{ $grn->grn_number }}</p>
                                <p class="po-doc-tile-sub">
                                    {{ $grn->received_at?->format('d M Y') ?? '—' }}
                                    @if ($grn->warehouse) · {{ $grn->warehouse->name }} @endif
                                </p>
                            </div>
                            <i class="fas fa-chevron-right po-doc-tile-arrow"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="po-sidebar-empty">
                    <i class="fas fa-box-open"></i>
                    <p>No receipts yet</p>
                    @if ($purchaseOrder->canReceive())
                        @can('procurement.manage')
                            <a href="{{ route('goods-receipts.create', $purchaseOrder) }}" class="mi-btn-orange mt-2 inline-flex text-xs py-1.5 px-2.5">
                                <i class="fas fa-plus text-[0.55rem]"></i> Receive Goods
                            </a>
                        @endcan
                    @endif
                </div>
            @endif
        </div>

    </div>
</aside>
