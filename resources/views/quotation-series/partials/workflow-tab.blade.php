@php
    $statusOrder = ['quotation_draft', 'order_draft', 'approved', 'po_generated', 'in_transit', 'received', 'closed'];
    $currentIdx = array_search($series->status, $statusOrder, true);
    if ($currentIdx === false) {
        $currentIdx = 0;
    }
    $showInTransitStep = in_array($series->status, ['approved', 'po_generated'], true);
    $inTransitPending = $series->status === 'approved' && ! $series->purchaseOrders()->exists();
@endphp

<div class="qs-grid-2 qs-workflow-grid">
    <div class="space-y-4">
        @can('procurement.manage')
            @if ($series->canGeneratePo() || $showInTransitStep || $series->canCloseSeries())
                <div class="mi-card qs-workflow-actions-card">
                    <div class="mi-card-head">
                        <div>
                            <div class="qs-section-title"><i class="fas fa-bolt"></i> Actions</div>
                            <p class="qs-section-sub">Complete each step in order</p>
                        </div>
                    </div>
                    <div class="qs-workflow-actions">
                        @if ($series->canGeneratePo())
                            <div class="qs-workflow-action qs-workflow-action--primary">
                                <div class="qs-workflow-action-body">
                                    <p class="qs-workflow-action-title">Generate Purchase Order</p>
                                    <p class="qs-workflow-action-desc">Create a PO from the approved order lines.</p>
                                </div>
                                <form action="{{ route('quotation-series.generate-po', $series) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="mi-btn-orange text-xs"><i class="fas fa-file-invoice"></i> Generate PO</button>
                                </form>
                            </div>
                        @endif

                        @if ($showInTransitStep)
                            <div class="qs-workflow-action qs-workflow-action--transit {{ $inTransitPending ? 'is-disabled' : '' }}">
                                <div class="qs-workflow-action-body">
                                    <p class="qs-workflow-action-title">Mark In Transit</p>
                                    <p class="qs-workflow-action-desc">
                                        @if ($inTransitPending)
                                            Generate a purchase order first, then mark goods as in transit.
                                        @else
                                            Goods have left the supplier and are on the way.
                                        @endif
                                    </p>
                                </div>
                                @if ($series->canMarkInTransit())
                                    <form action="{{ route('quotation-series.in-transit', $series) }}" method="POST" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="mi-btn-ghost text-xs"><i class="fas fa-truck"></i> In Transit</button>
                                    </form>
                                @else
                                    <button type="button"
                                            class="mi-btn-ghost text-xs opacity-50 cursor-not-allowed"
                                            disabled
                                            title="Generate a purchase order first">
                                        <i class="fas fa-truck"></i> In Transit
                                    </button>
                                @endif
                            </div>
                        @endif

                        @if ($series->canCloseSeries())
                            <div class="qs-workflow-action qs-workflow-action--close">
                                <div class="qs-workflow-action-body">
                                    <p class="qs-workflow-action-title">Finalize Series</p>
                                    <p class="qs-workflow-action-desc">All POs are received — close this series to mark it complete.</p>
                                </div>
                                <form action="{{ route('quotation-series.close', $series) }}" method="POST" class="shrink-0" data-confirm="Close this quotation series? All purchase orders must be fully received.">
                                    @csrf
                                    <button type="submit" class="mi-btn-ghost text-xs"><i class="fas fa-flag-checkered"></i> Close</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endcan

        @if ($series->approval)
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="qs-section-title"><i class="fas fa-stamp"></i> Approval</div>
                </div>
                <div class="qs-workflow-card-body">
                    <a href="{{ route('approvals.show', $series->approval) }}" class="qs-link-card">
                        <div class="flex items-center gap-3">
                            <div class="qs-link-card-icon bg-blue-50 text-blue-600"><i class="fas fa-stamp"></i></div>
                            <div>
                                <p class="text-sm font-semibold">Approval #{{ $series->approval->id }}</p>
                                <p class="text-xs text-gray-500">View approval record</p>
                            </div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-300"></i>
                    </a>
                </div>
            </div>
        @endif

        @if ($series->purchaseOrders->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="qs-section-title"><i class="fas fa-file-invoice"></i> Purchase Orders</div>
                </div>
                <div class="qs-workflow-card-body space-y-2">
                    @foreach ($series->purchaseOrders as $po)
                        <a href="{{ route('purchase-orders.show', $po) }}" class="qs-link-card">
                            <div class="flex items-center gap-3">
                                <div class="qs-link-card-icon bg-indigo-50 text-indigo-600"><i class="fas fa-file-invoice"></i></div>
                                <div>
                                    <p class="text-sm font-semibold">{{ $po->po_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $po->statusLabel() }}</p>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-300"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($series->goodsReceiptNotes->isNotEmpty())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="qs-section-title"><i class="fas fa-box-open"></i> Goods Receipts</div>
                </div>
                <div class="qs-workflow-card-body space-y-2">
                    @foreach ($series->goodsReceiptNotes as $grn)
                        <a href="{{ route('goods-receipts.show', $grn) }}"
                           class="qs-link-card {{ $grn->isVoided() ? 'qs-link-card--voided' : '' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="qs-link-card-icon bg-green-50 text-green-600"><i class="fas fa-box-open"></i></div>
                                <div class="min-w-0">
                                    <p class="qs-link-card-title">{{ $grn->grn_number }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if ($grn->isVoided())
                                            <span class="qs-grn-badge-voided"><i class="fas fa-ban text-[0.5rem]"></i> Voided</span>
                                            · Reversed from stock
                                        @else
                                            Posted · {{ $grn->received_at?->format('d M Y') ?? 'Goods receipt note' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-300 shrink-0"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="mi-card qs-workflow-timeline-card">
        <div class="mi-card-head">
            <div class="qs-section-title"><i class="fas fa-timeline"></i> Status Timeline</div>
        </div>
        <div class="qs-workflow-timeline-body">
            <div class="qs-timeline">
                @foreach ($workflowSteps as $i => $step)
                    @php
                        $stepIdx = array_search($step['key'], $statusOrder, true);
                        $isDone = $stepIdx !== false && $stepIdx < $currentIdx;
                        $isCurrent = $series->status === $step['key']
                            || ($step['key'] === 'received' && in_array($series->status, ['received', 'closed'], true))
                            || ($step['key'] === 'closed' && $series->status === 'closed');
                    @endphp
                    <div class="qs-timeline-item {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="qs-timeline-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                        <div class="qs-timeline-body">
                            <p class="qs-timeline-title">{{ $step['label'] }}</p>
                            <p class="qs-timeline-desc">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
