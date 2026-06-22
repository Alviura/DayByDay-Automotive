@if ($goodsReceiptNote->isVoided())
    <div class="grn-void-record">
        <div class="grn-void-record-icon"><i class="fas fa-ban"></i></div>
        <div class="min-w-0 flex-1">
            <p class="grn-void-record-title">
                Voided on {{ $goodsReceiptNote->voided_at?->format('d M Y, H:i') }}
                @if ($goodsReceiptNote->voidedBy)
                    <span class="grn-void-record-meta">· {{ $goodsReceiptNote->voidedBy->name }}</span>
                @endif
            </p>
            @if ($goodsReceiptNote->void_reason)
                <p class="grn-void-record-reason">{{ $goodsReceiptNote->void_reason }}</p>
            @endif
            <p class="grn-void-record-note">Inventory has been reversed and PO received quantities rolled back.</p>
        </div>
    </div>
@endif

@if ($goodsReceiptNote->canVoid())
    @can('procurement.manage')
        <div
            x-ref="voidPanel"
            x-show="voidOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="grn-void-panel"
        >
            <div class="grn-void-panel-head">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="grn-void-panel-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="min-w-0">
                        <p class="grn-void-panel-title">Void goods receipt</p>
                        <p class="grn-void-panel-sub">This action is permanent and cannot be undone.</p>
                    </div>
                </div>
                <button type="button" class="grn-void-panel-close" @click="voidOpen = false" aria-label="Close void panel">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="grn-void-panel-body">
                <div class="grn-void-impact">
                    <p class="grn-void-impact-title"><i class="fas fa-list-check"></i> What will happen</p>
                    <ul class="grn-void-impact-list">
                        <li>
                            <span class="grn-void-impact-stat">{{ number_format($goodQty, 0) }}</span>
                            good units removed from <strong>{{ $goodsReceiptNote->warehouse?->name ?? 'warehouse' }}</strong> inventory
                        </li>
                        <li>
                            PO received quantities rolled back for
                            <strong>{{ $lineCount }} {{ str('line')->plural($lineCount) }}</strong>
                            @if ($goodsReceiptNote->purchaseOrder)
                                on <strong>{{ $goodsReceiptNote->purchaseOrder->po_number }}</strong>
                            @endif
                        </li>
                        @if ($damagedQty > 0)
                            <li>
                                <span class="grn-void-impact-stat">{{ number_format($damagedQty, 0) }}</span>
                                damaged units recorded on this receipt will be cleared
                            </li>
                        @endif
                        <li>Inventory value of <strong>{{ number_format($goodsReceiptNote->totalValue(), 2) }} KES</strong> will be reversed</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('goods-receipts.void', $goodsReceiptNote) }}" class="grn-void-form"
                      data-confirm="Void {{ $goodsReceiptNote->grn_number }}? Stock will be reversed immediately."
                      data-confirm-variant="danger"
                      data-confirm-label="Void receipt">
                    @csrf
                    <div>
                        <label for="grn-void-reason" class="mi-field-label">Reason for voiding <span class="text-rose-500">*</span></label>
                        <textarea
                            id="grn-void-reason"
                            name="reason"
                            rows="4"
                            class="mi-input block w-full grn-void-textarea"
                            required
                            minlength="10"
                            maxlength="1000"
                            placeholder="Describe why this receipt must be voided — e.g. wrong warehouse, duplicate entry, quantity error…"
                        >{{ old('reason') }}</textarea>
                        <p class="grn-void-field-hint">Minimum 10 characters. This is stored in the audit trail.</p>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1.5" />
                    </div>
                    <div class="grn-void-form-actions">
                        <button type="button" class="mi-btn-ghost" @click="voidOpen = false">Keep Receipt</button>
                        <button type="submit" class="grn-btn-danger">
                            <i class="fas fa-ban text-xs"></i> Confirm Void
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endif
