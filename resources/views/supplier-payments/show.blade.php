<x-app-layout :title="$supplierPayment->payment_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('supplier-payments.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold sp-mono text-gray-900 leading-tight">{{ $supplierPayment->payment_number }}</h1>
                        <span class="{{ $supplierPayment->statusBadgeClass() }}">{{ $supplierPayment->statusLabel() }}</span>
                        <span class="sp-method-pill {{ $supplierPayment->methodPillClass() }}">
                            <i class="fas {{ $supplierPayment->methodIcon() }}"></i>
                            {{ $supplierPayment->methodLabel() }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">
                        @if ($supplierPayment->supplier)
                            <a href="{{ route('suppliers.show', $supplierPayment->supplier) }}" class="text-orange-600 hover:underline font-semibold">{{ $supplierPayment->supplier->name }}</a>
                        @endif
                        · {{ $supplierPayment->paid_at?->format('l, d M Y H:i') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Print</button>
                <a href="{{ route('supplier-payments.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> All Payments</a>
            </div>
        </div>

        @include('supplier-payments.partials.nav-tabs', ['active' => 'index'])

        @if ($supplierPayment->isVoided())
            <div class="sp-show-banner sp-show-banner-voided no-print">
                <i class="fas fa-ban"></i>
                <span>Voided {{ $supplierPayment->voided_at?->format('d M Y H:i') }}
                    @if ($supplierPayment->voidedBy) by {{ $supplierPayment->voidedBy->name }} @endif
                    — {{ $supplierPayment->void_reason }}</span>
            </div>
        @elseif ($supplierPayment->isPosted())
            <div class="sp-show-banner sp-show-banner-posted no-print">
                <i class="fas fa-circle-check"></i>
                <span>Posted to accounts payable — KES {{ number_format($supplierPayment->amount, 2) }} cleared from supplier balance</span>
            </div>
        @endif

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Amount Paid</p>
                    <p class="mi-kpi-value sp-amt">{{ number_format($supplierPayment->amount, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Allocation</p>
                    <p class="mi-kpi-value text-base">{{ $supplierPayment->allocationLabel() }}</p>
                    <p class="sp-kpi-sub">{{ strtoupper($supplierPayment->allocationType()) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-link"></i></div>
            </div>
            @if ($allocationContext)
                <div class="mi-kpi {{ $allocationContext['remaining'] > 0 ? 'mi-kpi-amber' : 'mi-kpi-green' }}">
                    <div>
                        <p class="mi-kpi-label">GRN Balance Remaining</p>
                        <p class="mi-kpi-value sp-amt">{{ number_format($allocationContext['remaining'], 2) }}</p>
                        <p class="sp-kpi-sub">of KES {{ number_format($allocationContext['grn_value'], 2) }} GRN value</p>
                    </div>
                    <div class="mi-kpi-icon"><i class="fas fa-scale-balanced"></i></div>
                </div>
            @endif
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Recorded By</p>
                    <p class="mi-kpi-value text-base">{{ $supplierPayment->payer?->name ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user"></i></div>
            </div>
        </div>

        <div class="sp-show-grid">
            <div class="space-y-5">
                <div class="sp-doc-card">
                    <div class="sp-doc-head">
                        <div>
                            <h2>Payment Details</h2>
                            <p>Settlement against supplier accounts payable</p>
                        </div>
                        <p class="sp-amt sp-amt-lg">KES {{ number_format($supplierPayment->amount, 2) }}</p>
                    </div>
                    <div class="sp-doc-body">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-truck"></i> Supplier</span>
                                <span class="mi-show-meta-value">{{ $supplierPayment->supplier?->name }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas {{ $supplierPayment->methodIcon() }}"></i> Method</span>
                                <span class="mi-show-meta-value">{{ $supplierPayment->methodLabel() }}</span>
                            </li>
                            @if ($supplierPayment->reference)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-hashtag"></i> Reference</span>
                                    <span class="mi-show-meta-value sp-mono">{{ $supplierPayment->reference }}</span>
                                </li>
                            @endif
                            @if ($supplierPayment->supplier_invoice_number)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-file-invoice"></i> Supplier invoice</span>
                                    <span class="mi-show-meta-value sp-mono">{{ $supplierPayment->supplier_invoice_number }}</span>
                                </li>
                            @endif
                            @if ($supplierPayment->goodsReceiptNote)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-truck-ramp-box"></i> GRN</span>
                                    <span class="mi-show-meta-value">
                                        <a href="{{ route('goods-receipts.show', $supplierPayment->goodsReceiptNote) }}" class="text-orange-600 hover:underline">{{ $supplierPayment->goodsReceiptNote->grn_number }}</a>
                                        @if ($supplierPayment->goodsReceiptNote->warehouse)
                                            · {{ $supplierPayment->goodsReceiptNote->warehouse->name }}
                                        @endif
                                    </span>
                                </li>
                            @endif
                            @if ($supplierPayment->purchaseOrder)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-file-invoice-dollar"></i> PO</span>
                                    <span class="mi-show-meta-value">
                                        <a href="{{ route('purchase-orders.show', $supplierPayment->purchaseOrder) }}" class="text-orange-600 hover:underline">{{ $supplierPayment->purchaseOrder->po_number }}</a>
                                    </span>
                                </li>
                            @endif
                            @if ($supplierPayment->notes)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-note-sticky"></i> Notes</span>
                                    <span class="mi-show-meta-value">{{ $supplierPayment->notes }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if ($allocationContext)
                    <div class="sp-doc-card">
                        <div class="sp-doc-head">
                            <div>
                                <h2>GRN Settlement</h2>
                                <p>How this payment applies to the goods receipt</p>
                            </div>
                        </div>
                        <div class="sp-doc-body">
                            <div class="sp-alloc-summary">
                                <div class="sp-alloc-stat">
                                    <p class="sp-alloc-stat-label">GRN Value</p>
                                    <p class="sp-alloc-stat-value">{{ number_format($allocationContext['grn_value'], 2) }}</p>
                                </div>
                                <div class="sp-alloc-stat">
                                    <p class="sp-alloc-stat-label">This Payment</p>
                                    <p class="sp-alloc-stat-value accent">{{ number_format($allocationContext['cleared_by_this'], 2) }}</p>
                                </div>
                                <div class="sp-alloc-stat">
                                    <p class="sp-alloc-stat-label">Still Due</p>
                                    <p class="sp-alloc-stat-value">{{ number_format($allocationContext['remaining'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($relatedPayments->isNotEmpty())
                    <div class="sp-doc-card">
                        <div class="sp-doc-head">
                            <div>
                                <h2>Other Payments on this GRN</h2>
                                <p>{{ $relatedPayments->count() }} related record{{ $relatedPayments->count() === 1 ? '' : 's' }}</p>
                            </div>
                        </div>
                        <div class="sp-doc-body">
                            @foreach ($relatedPayments as $related)
                                <div class="sp-related-row">
                                    <div>
                                        <a href="{{ route('supplier-payments.show', $related) }}" class="sp-mono text-sm text-orange-600 hover:underline">{{ $related->payment_number }}</a>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $related->paid_at?->format('d M Y') }} · {{ $related->payer?->name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="sp-amt">{{ number_format($related->amount, 2) }}</p>
                                        <span class="{{ $related->statusBadgeClass() }}">{{ $related->statusLabel() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="mi-guide no-print">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-bolt"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Actions</h2>
                        <p class="mi-guide-subtitle">Payment management</p>
                    </div>
                </div>
                <div class="mi-guide-body space-y-4">
                    @if ($supplierPayment->supplier)
                        <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplierPayment->supplier_id]) }}" class="mi-btn-orange w-full justify-center">
                            <i class="fas fa-plus text-xs"></i> Pay supplier again
                        </a>
                    @endif

                    @if ($journalEntry)
                        @can('finance.view')
                            <a href="{{ route('journal-entries.show', $journalEntry) }}" class="mi-btn-ghost w-full justify-center">
                                <i class="fas fa-book text-xs"></i> View GL Journal
                            </a>
                        @endcan
                    @endif

                    @can('supplier_payments.manage')
                        @if ($supplierPayment->canVoid())
                            <form method="POST" action="{{ route('supplier-payments.void', $supplierPayment) }}" class="space-y-2 border-t border-gray-100 pt-4"
                                  data-confirm="Void this payment? The supplier balance will be restored." data-confirm-variant="danger">
                                @csrf
                                <label class="mi-field-label">Void reason</label>
                                <textarea name="void_reason" rows="2" class="mi-input w-full" required placeholder="Why is this payment being voided?"></textarea>
                                @error('void_reason')<p class="mi-field-error">{{ $message }}</p>@enderror
                                <button type="submit" class="mi-btn-danger w-full justify-center">
                                    <i class="fas fa-ban text-xs"></i> Void Payment
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
