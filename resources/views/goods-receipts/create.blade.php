@php
    $receivableItems = $purchaseOrder->items->filter(fn ($item) => $item->remainingQuantity() > 0);
    $totalRemaining = $receivableItems->sum(fn ($item) => $item->remainingQuantity());
    $receiptPct = $purchaseOrder->receiptProgressPercent();
@endphp

<x-app-layout :title="'Receive '.$purchaseOrder->po_number">
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
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Receive Goods</h1>
                        <span class="grn-badge grn-badge-emerald">New GRN</span>
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="mi-cat-badge hover:border-blue-300">
                            <i class="fas fa-file-invoice-dollar text-[0.55rem]"></i> {{ $purchaseOrder->po_number }}
                        </a>
                        <span>{{ $purchaseOrder->supplier?->name }}</span>
                        @if ($purchaseOrder->quotationSeries)
                            <span class="mi-cat-badge">{{ $purchaseOrder->quotationSeries->displayName() }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to PO
            </a>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Lines to Receive</p>
                    <p class="mi-kpi-value">{{ $receivableItems->count() }}</p>
                    <p class="grn-kpi-sub">With remaining quantity</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Remaining Units</p>
                    <p class="mi-kpi-value">{{ number_format($totalRemaining, 0) }}</p>
                    <p class="grn-kpi-sub">Across all open lines</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-box"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">PO Progress</p>
                    <p class="mi-kpi-value">{{ $receiptPct }}%</p>
                    <p class="grn-kpi-sub">Already received on this PO</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">PO Total</p>
                    <p class="mi-kpi-value orange" style="font-size:1.15rem">{{ number_format($purchaseOrder->total, 2) }}</p>
                    <p class="grn-kpi-sub">{{ $purchaseOrder->currency }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>

        <div class="grn-phase-banner grn-phase-banner-emerald">
            <i class="fas fa-lightbulb"></i>
            <div>
                <strong>Post goods to inventory.</strong>
                Enter received and damaged quantities per line. Good stock (received minus damaged) will be added to the selected warehouse at the unit costs shown.
            </div>
        </div>

        {{-- Form + guide --}}
        <div class="mi-form-split">
            <form method="POST" action="{{ route('goods-receipts.store', $purchaseOrder) }}" class="mi-card mi-form-main">
                @csrf
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-pen-to-square text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Receipt Entry</span>
                    </div>
                    <span class="mi-cat-badge">Post to Inventory</span>
                </div>

                <div class="mi-form-body space-y-5">
                    <div class="mi-form-grid">
                        <div>
                            <label class="mi-field-label"><i class="fas fa-warehouse"></i> Receive into Warehouse <span class="text-rose-500">*</span></label>
                            @if ($scopedWarehouseId ?? null)
                                <input type="hidden" name="warehouse_id" value="{{ $scopedWarehouseId }}">
                                <div class="mi-input block w-full bg-gray-50 text-gray-700 cursor-not-allowed">
                                    {{ $warehouses->firstWhere('id', $scopedWarehouseId)?->name }}
                                </div>
                            @else
                            <select name="warehouse_id" class="mi-select" required>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $defaultWarehouseId ?? null) == $warehouse->id)>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})
                                    </option>
                                @endforeach
                            </select>
                            @endif
                            <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1.5" />
                        </div>
                        <div class="mi-span-full">
                            <label class="mi-field-label">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea name="notes" rows="2" class="mi-input block w-full" placeholder="Delivery reference, condition notes…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th class="w-10">Include</th>
                                    <th>Product</th>
                                    <th>Remaining</th>
                                    <th>Received Qty</th>
                                    <th>Damaged Qty</th>
                                    <th>Unit Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $formIndex = 0; @endphp
                                @foreach ($purchaseOrder->items as $item)
                                    @if ($item->remainingQuantity() > 0)
                                        <tr x-data="{ included: true }">
                                            <td class="text-center">
                                                <input type="checkbox" name="items[{{ $formIndex }}][include]" value="1" x-model="included" class="rounded border-gray-300">
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-gray-800" :class="{ 'opacity-40': !included }">{{ $item->product->part_number }}</span>
                                                <p class="mi-pkg-sub" :class="{ 'opacity-40': !included }">{{ $item->product->name }}</p>
                                                <input type="hidden" name="items[{{ $formIndex }}][product_id]" value="{{ $item->product_id }}">
                                            </td>
                                            <td><span class="font-semibold text-gray-700" :class="{ 'opacity-40': !included }">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($item->remainingQuantity()) }}</span></td>
                                            <td>
                                                <input type="number" step="1" min="0"
                                                       name="items[{{ $formIndex }}][received_quantity]"
                                                       class="mi-input grn-input-qty"
                                                       :disabled="!included"
                                                       value="{{ old('items.'.$formIndex.'.received_quantity', \App\Models\GoodsReceiptNoteItem::normalizeQuantity($item->remainingQuantity())) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="1" min="0"
                                                       name="items[{{ $formIndex }}][damaged_quantity]"
                                                       class="mi-input grn-input-qty"
                                                       :disabled="!included"
                                                       value="{{ old('items.'.$formIndex.'.damaged_quantity', 0) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0"
                                                       name="items[{{ $formIndex }}][unit_cost]"
                                                       class="mi-input grn-input-cost"
                                                       :disabled="!included"
                                                       value="{{ old('items.'.$formIndex.'.unit_cost', $item->unit_cost) }}">
                                            </td>
                                        </tr>
                                        @php $formIndex++; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mi-form-actions">
                    <x-input-error :messages="$errors->get('items')" class="mr-auto" />
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="mi-btn-ghost">Cancel</a>
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-check text-xs"></i> Post Receipt to Inventory
                    </button>
                </div>
            </form>

            <x-module.form-guide subtitle="Receiving goods against a PO">
                <div class="grn-po-summary mb-4">
                    <p class="grn-po-summary-label">Purchase order</p>
                    <p class="text-sm font-bold text-blue-900">{{ $purchaseOrder->po_number }}</p>
                    <p class="text-xs text-blue-700 mt-1">{{ $purchaseOrder->supplier?->name }} · {{ $receiptPct }}% already received</p>
                </div>

                <ol class="mi-guide-list">
                    <li>Check <strong>Include</strong> only for products on this delivery.</li>
                    <li>Select the <strong>warehouse</strong> where goods will be stored.</li>
                    <li>Enter <strong>received qty</strong> for each included line.</li>
                    <li>Record any <strong>damaged qty</strong>; only good stock hits inventory.</li>
                    <li>If the supplier will never ship the balance, use <strong>Close Short</strong> on the PO after posting.</li>
                </ol>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">After posting</p>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        You'll be taken to the GRN detail page. The PO receipt progress updates automatically; partial receipts are supported.
                    </p>
                </div>
            </x-module.form-guide>
        </div>
    </div>
</x-app-layout>
