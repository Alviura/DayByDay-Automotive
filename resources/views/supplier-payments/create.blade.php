<x-app-layout title="Record Supplier Payment">

    @push('styles')
        <x-module.page-index-styles />
        @include('supplier-payments.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{
        supplierId: '{{ old('supplier_id', $selectedSupplier?->id) }}',
        grnId: '{{ old('goods_receipt_note_id', $selectedGrn?->id ?? '') }}',
        method: '{{ old('method', 'bank_transfer') }}',
        amount: '{{ old('amount', $balanceDue ? number_format($balanceDue, 2, '.', '') : '') }}'
    }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Record Supplier Payment</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Pay against a posted goods receipt or supplier balance.</p>
                </div>
            </div>
            <a href="{{ route('supplier-payments.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </div>

        @include('supplier-payments.partials.nav-tabs', ['active' => 'create'])

        <form method="POST" action="{{ route('supplier-payments.store') }}" class="sp-create-grid">
            @csrf

            <div class="space-y-5">
                <div class="sp-form-section">
                    <div class="sp-form-section-head">
                        <span class="sp-step">1</span> Select supplier
                    </div>
                    <div class="sp-form-section-body">
                        <label class="mi-field-label" for="supplier_id">Supplier <span class="text-rose-500">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="mi-select w-full" required
                                @change="if ($event.target.value) window.location='{{ route('supplier-payments.create') }}?supplier_id=' + $event.target.value">
                            <option value="">Select supplier…</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedSupplier?->id) == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<p class="mi-field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                @if ($selectedSupplier)
                    @if ($balanceDue !== null)
                        <div class="sp-balance-banner">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-orange-800/70">Open GRN balance</p>
                                <p class="text-sm text-gray-700 mt-0.5">
                                    @if ($selectedGrn)
                                        Selected GRN after prior payments
                                    @elseif ($selectedPo)
                                        Purchase order total due
                                    @else
                                        Sum of unsettled goods receipts (oldest paid first)
                                    @endif
                                </p>
                            </div>
                            <strong>KES {{ number_format($balanceDue, 2) }}</strong>
                        </div>
                    @endif

                    <div class="sp-form-section">
                        <div class="sp-form-section-head">
                            <span class="sp-step">2</span> Allocate payment
                        </div>
                        <div class="sp-form-section-body space-y-3">
                            <p class="text-sm text-gray-500">Choose a goods receipt to settle, or pay on the supplier account.</p>

                            <input type="hidden" name="goods_receipt_note_id" :value="grnId">

                            <div class="sp-grn-picker">
                                <label class="sp-grn-option" :class="{ 'selected': grnId === '' }" @click="grnId = ''">
                                    <input type="radio" name="_grn_pick" value="" :checked="grnId === ''">
                                    <div>
                                        <p class="font-semibold text-sm text-gray-900">Pay on supplier account</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Not tied to a specific GRN</p>
                                    </div>
                                </label>

                                @foreach ($openGrns as $row)
                                    @php $grn = $row['grn']; @endphp
                                    <label class="sp-grn-option"
                                           :class="{ 'selected': grnId === '{{ $grn->id }}' }"
                                           @click="grnId = '{{ $grn->id }}'; amount = '{{ number_format($row['balance'], 2, '.', '') }}'">
                                        <input type="radio" name="_grn_pick" value="{{ $grn->id }}" :checked="grnId === '{{ $grn->id }}'">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-sm text-gray-900">{{ $grn->grn_number }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">
                                                {{ $grn->purchaseOrder?->po_number }}
                                                · {{ $grn->received_at?->format('d M Y') }}
                                            </p>
                                        </div>
                                        <span class="sp-amt text-sm whitespace-nowrap">{{ number_format($row['balance'], 2) }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @if ($openGrns->isEmpty())
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">No open GRN balances for this supplier — payment will apply on account.</p>
                            @endif

                            @if ($selectedPo && ! $selectedGrn)
                                <input type="hidden" name="purchase_order_id" value="{{ $selectedPo->id }}">
                            @endif

                            @error('goods_receipt_note_id')<p class="mi-field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="sp-form-section">
                        <div class="sp-form-section-head">
                            <span class="sp-step">3</span> Payment amount &amp; method
                        </div>
                        <div class="sp-form-section-body space-y-4">
                            <div>
                                <label class="mi-field-label" for="amount">Amount (KES) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="mi-input w-full text-lg font-bold" required x-model="amount">
                                @error('amount')<p class="mi-field-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="mi-field-label">Payment method <span class="text-rose-500">*</span></label>
                                <input type="hidden" name="method" :value="method">
                                <div class="sp-method-grid">
                                    @foreach ($paymentMethods as $value => $label)
                                        @php
                                            $icon = match ($value) {
                                                'cash' => 'fa-money-bill-wave',
                                                'mpesa' => 'fa-mobile-screen',
                                                'card' => 'fa-credit-card',
                                                'bank_transfer' => 'fa-building-columns',
                                                'cheque' => 'fa-money-check',
                                                default => 'fa-wallet',
                                            };
                                        @endphp
                                        <label class="sp-method-option" :class="{ 'selected': method === '{{ $value }}' }" @click="method = '{{ $value }}'">
                                            <input type="radio" :checked="method === '{{ $value }}'">
                                            <i class="fas {{ $icon }}"></i>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('method')<p class="mi-field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="sp-form-section">
                        <div class="sp-form-section-head">
                            <span class="sp-step">4</span> References &amp; notes
                        </div>
                        <div class="sp-form-section-body space-y-4">
                            <div>
                                <label class="mi-field-label" for="supplier_invoice_number">Supplier invoice #</label>
                                <input type="text" name="supplier_invoice_number" id="supplier_invoice_number" class="mi-input w-full"
                                       value="{{ old('supplier_invoice_number') }}" placeholder="Supplier tax invoice reference">
                                @error('supplier_invoice_number')<p class="mi-field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mi-field-label" for="reference">Payment reference</label>
                                <input type="text" name="reference" id="reference" class="mi-input w-full"
                                       value="{{ old('reference') }}" placeholder="Cheque #, M-Pesa code, bank ref…">
                                @error('reference')<p class="mi-field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mi-field-label" for="notes">Notes</label>
                                <textarea name="notes" id="notes" rows="2" class="mi-input w-full">{{ old('notes') }}</textarea>
                                @error('notes')<p class="mi-field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="mi-btn-orange">
                            <i class="fas fa-check text-xs"></i> Record Payment
                        </button>
                        <a href="{{ route('supplier-payments.index') }}" class="mi-btn-ghost">Cancel</a>
                    </div>
                @endif
            </div>

            <aside class="mi-guide no-print">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">How it works</h2>
                        <p class="mi-guide-subtitle">Supplier AP settlement</p>
                    </div>
                </div>
                <div class="mi-guide-body text-sm text-gray-600 space-y-3">
                    <p><strong class="text-gray-800">1.</strong> Pick the supplier you are paying.</p>
                    <p><strong class="text-gray-800">2.</strong> Allocate to a posted GRN when possible — on-account payments are applied to the oldest open GRNs first.</p>
                    <p><strong class="text-gray-800">3.</strong> Enter the amount and how you paid (bank, M-Pesa, etc.).</p>
                    <p><strong class="text-gray-800">4.</strong> A GL journal is posted automatically (Dr GRNI / Cr Bank).</p>
                    @if ($selectedSupplier && $balanceDue !== null)
                        <div class="border-t border-gray-100 pt-3 mt-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Current balance</p>
                            <p class="sp-amt text-lg text-orange-700">KES {{ number_format($balanceDue, 2) }}</p>
                        </div>
                    @endif
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>
