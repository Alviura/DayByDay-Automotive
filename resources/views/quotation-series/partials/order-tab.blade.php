@if ($series->status === 'order_draft' && $series->items->isNotEmpty())
    @php
        $pricesPanelExpanded = ! $series->hasSavedPrices() || $errors->any();
    @endphp
    <div class="space-y-4" x-data="orderProcessingPanel(@js($series->hasSavedPrices()), @js($pricesPanelExpanded))" x-init="init()">

        @if ($series->canEditPrices())
            @can('procurement.manage')
                <div class="mi-card qs-collapsible-card" :class="{ 'is-collapsed': !pricesExpanded }">
                    <div class="mi-card-head qs-collapsible-head" @click="togglePricesPanel()">
                        <div class="min-w-0 flex-1">
                            <div class="qs-section-title"><i class="fas fa-pen-to-square"></i> Enter Supplier Prices</div>
                            <p class="qs-section-sub" x-show="pricesExpanded">
                                {{ $series->isImport()
                                    ? 'Foreign unit prices, dimensions, and qty per packet — MKT wholesale defaults from product min price'
                                    : 'Unit prices and per-line transport in KES — MKT wholesale defaults from product min price' }}
                            </p>
                            <p class="qs-section-sub" x-show="!pricesExpanded" x-cloak>
                                @if ($series->isCalculated())
                                    {{ $series->items->count() }} lines priced · margins calculated
                                @elseif ($series->hasSavedPrices())
                                    {{ $series->items->count() }} lines priced · ready to calculate margins
                                @else
                                    {{ $series->items->count() }} lines · click to enter supplier prices
                                @endif
                            </p>
                        </div>
                        <button type="button" class="mi-btn-ghost text-xs shrink-0" @click.stop="togglePricesPanel()">
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': pricesExpanded }"></i>
                            <span x-text="pricesExpanded ? 'Collapse' : 'Edit prices'"></span>
                        </button>
                    </div>
                    <div x-show="pricesExpanded" x-cloak>
                    <form method="POST"
                          action="{{ route('quotation-series.items.prices', $series) }}"
                          @change="markDirty()"
                          @submit="onPricesFormSubmit()">
                        @csrf
                        @method('PATCH')
                        <div class="mi-table-wrap overflow-x-auto">
                            <table class="mi-table text-sm">
                                <thead>
                                    <tr>
                                        <th>Part Number</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        @if ($series->isImport())
                                            <th>Unit ({{ $series->currency }})</th>
                                            <th>Qty/Packet</th>
                                            <th>Packets</th>
                                            <th>W × L × H</th>
                                        @else
                                            <th>Unit (KES)</th>
                                            <th>Transport</th>
                                        @endif
                                        <th>MKT Wholesale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($series->items as $index => $item)
                                        @php
                                            $qty = (float) $item->quantity;
                                            $qtyPerPacket = (float) (old('items.'.$index.'.quantity_per_packet', $item->quantity_per_packet ?: 1) ?: 1);
                                            $derivedPackets = \App\Services\Procurement\ImportOrderCalculator::deriveNumberOfPackets($qty, $qtyPerPacket);
                                            $savedPackets = old('items.'.$index.'.number_of_packets', $item->number_of_packets);
                                            $packetsOverride = (bool) old('items.'.$index.'.packets_override', $savedPackets !== null && abs((float) $savedPackets - $derivedPackets) > 0.009);
                                            $productMkt = (float) $item->product->min_selling_price;
                                            $savedMkt = old('items.'.$index.'.market_wholesale_price', $item->market_wholesale_price);
                                            $mktOverride = (bool) old('items.'.$index.'.market_wholesale_override', $savedMkt !== null && abs((float) $savedMkt - $productMkt) > 0.009);
                                        @endphp
                                        <tr x-data="orderPriceLine(@js([
                                            'isImport' => $series->isImport(),
                                            'quantity' => $qty,
                                            'qtyPerPacket' => $qtyPerPacket,
                                            'savedPackets' => $savedPackets !== null ? (float) $savedPackets : null,
                                            'overridePackets' => $packetsOverride,
                                            'productMkt' => $productMkt,
                                            'savedMkt' => $savedMkt !== null ? (float) $savedMkt : null,
                                            'overrideMkt' => $mktOverride,
                                        ]))" x-init="init()">
                                            <td class="font-medium">{{ $item->product->part_number }}</td>
                                            <td class="text-gray-500 max-w-[10rem] truncate">{{ $item->product->productName?->name ?? $item->product->name }}</td>
                                            <td><span class="mi-cat-badge">{{ number_format($item->quantity, 0) }}</span></td>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            @if ($series->isImport())
                                                <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][unit_price_foreign]" value="{{ old('items.'.$index.'.unit_price_foreign', $item->unit_price_foreign ?? $item->unit_price) }}" class="mi-input qs-order-input"></td>
                                                <td>
                                                    <input type="number" step="0.01" min="0.01" name="items[{{ $index }}][quantity_per_packet]" x-model.number="qtyPerPacket" @input="onQtyPerPacketChange()" class="mi-input w-20">
                                                </td>
                                                <td>
                                                    <div class="qs-lockable-field" :class="{ 'is-override': overridePackets }">
                                                        <input type="number"
                                                               step="0.01"
                                                               min="0"
                                                               name="items[{{ $index }}][number_of_packets]"
                                                               x-model.number="displayPackets"
                                                               :readonly="!overridePackets"
                                                               class="mi-input qs-lockable-input"
                                                               title="Qty ÷ Qty/Packet">
                                                        <input type="hidden" name="items[{{ $index }}][packets_override]" :value="overridePackets ? 1 : 0">
                                                        <button type="button"
                                                                class="qs-lockable-toggle"
                                                                @click="togglePacketsOverride($el)"
                                                                :title="overridePackets ? 'Manual override — click to auto-calculate' : 'Auto-calculated — click to override manually'"
                                                                :aria-label="overridePackets ? 'Switch to auto-calculated packets' : 'Switch to manual packet override'">
                                                            <i class="fas" :class="overridePackets ? 'fa-lock-open' : 'fa-lock'"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap">
                                                    <div class="flex items-center gap-1">
                                                        <input type="number" step="0.0001" min="0" name="items[{{ $index }}][width]" value="{{ old('items.'.$index.'.width', $item->width) }}" class="mi-input w-16" placeholder="W" title="Width">
                                                        <span class="text-gray-300">×</span>
                                                        <input type="number" step="0.0001" min="0" name="items[{{ $index }}][length]" value="{{ old('items.'.$index.'.length', $item->length) }}" class="mi-input w-16" placeholder="L" title="Length">
                                                        <span class="text-gray-300">×</span>
                                                        <input type="number" step="0.0001" min="0" name="items[{{ $index }}][height]" value="{{ old('items.'.$index.'.height', $item->height) }}" class="mi-input w-16" placeholder="H" title="Height">
                                                    </div>
                                                </td>
                                            @else
                                                <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" value="{{ old('items.'.$index.'.unit_price', $item->unit_price) }}" class="mi-input qs-order-input"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][transport]" value="{{ old('items.'.$index.'.transport', $item->transport ?? 0) }}" class="mi-input w-24"></td>
                                            @endif
                                            <td>
                                                <div class="qs-lockable-field qs-lockable-field--wide" :class="{ 'is-override': overrideMkt }">
                                                    <input type="number"
                                                           step="0.01"
                                                           min="0"
                                                           name="items[{{ $index }}][market_wholesale_price]"
                                                           x-model.number="displayMkt"
                                                           :readonly="!overrideMkt"
                                                           class="mi-input qs-lockable-input"
                                                           title="Product min selling price">
                                                    <input type="hidden" name="items[{{ $index }}][market_wholesale_override]" :value="overrideMkt ? 1 : 0">
                                                    <button type="button"
                                                            class="qs-lockable-toggle"
                                                            @click="toggleMktOverride($el)"
                                                            :title="overrideMkt ? 'Manual override — click to use product min price' : 'From product min price — click to override'"
                                                            :aria-label="overrideMkt ? 'Switch to product min wholesale price' : 'Switch to manual MKT wholesale override'">
                                                        <i class="fas" :class="overrideMkt ? 'fa-lock-open' : 'fa-lock'"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="qs-action-bar">
                            <span class="text-xs text-gray-400">{{ $series->items->count() }} lines · save before calculating</span>
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-floppy-disk text-xs"></i> Save Prices</button>
                        </div>
                    </form>
                    </div>
                </div>
            @endcan
        @endif

        <div class="mi-card">
            <div class="mi-card-head">
                <div class="qs-section-head w-full">
                    <div>
                        <div class="qs-section-title"><i class="fas fa-calculator"></i> Order Summary</div>
                        <p class="qs-section-sub">{{ ucfirst($series->purchase_type) }} · margin vs MKT wholesale</p>
                    </div>
                    @can('procurement.manage')
                        @if ($series->canEditPrices())
                            <form action="{{ route('quotation-series.calculate', $series) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="mi-btn-orange text-xs transition-opacity"
                                        :disabled="!canRunCalculate()"
                                        :class="{ 'opacity-45 cursor-not-allowed': !canRunCalculate() }"
                                        :title="calculateHint()">
                                    <i class="fas fa-calculator"></i> Calculate Margins
                                </button>
                            </form>
                        @elseif ($series->isCalculated())
                            <span class="mi-cat-badge qs-type-local">Calculated</span>
                        @endif
                    @endcan
                </div>
            </div>

            <div x-show="formDirty && pricesSaved" x-cloak class="qs-phase-banner qs-phase-banner-amber mx-4 mt-4 mb-0">
                <i class="fas fa-triangle-exclamation"></i>
                <div>You have unsaved price changes. Save supplier prices before calculating margins.</div>
            </div>

            <div x-show="!pricesSaved" x-cloak class="qs-summary-locked">
                <div class="qs-empty py-10">
                    <div class="qs-empty-icon"><i class="fas fa-lock"></i></div>
                    <p class="font-semibold text-gray-600">Order summary locked</p>
                    <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto">Enter and save supplier prices above to unlock margin calculation.</p>
                </div>
            </div>

            <div x-show="pricesSaved" class="qs-summary-body" :class="{ 'qs-summary-stale': formDirty && @js($series->isCalculated()) }">
                <div class="mi-table-wrap overflow-x-auto qs-summary-scroll">
                    <table class="mi-table text-sm">
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th>Qty</th>
                                @if ($series->isImport())
                                    <th>Unit ({{ $series->currency }})</th>
                                    <th>Total ({{ $series->currency }})</th>
                                    <th>Unit (KES)</th>
                                    <th>CBM</th>
                                    <th>Transport/U</th>
                                @else
                                    <th>Unit (KES)</th>
                                    <th>Total (KES)</th>
                                    <th>Transport</th>
                                @endif
                                <th>Arrival</th>
                                <th>MKT</th>
                                <th>Margin</th>
                                <th>%</th>
                                <th>Actual</th>
                                <th>Exp. Sales</th>
                                <th>Exp. Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($series->items as $item)
                                @php
                                    $marginPositive = $item->margin_amount !== null && $item->margin_amount >= 0;
                                @endphp
                                <tr>
                                    <td class="font-medium whitespace-nowrap">{{ $item->product->part_number }}</td>
                                    <td>{{ number_format($item->quantity, 0) }}</td>
                                    @if ($series->isImport())
                                        <td>{{ $item->unit_price_foreign ? number_format($item->unit_price_foreign, 4) : '—' }}</td>
                                        <td>{{ $item->total_purchase_price ? number_format($item->total_purchase_price, 2) : '—' }}</td>
                                        <td>{{ $item->unit_price_ksh ? number_format($item->unit_price_ksh, 2) : '—' }}</td>
                                        <td>{{ $item->total_cbm ? number_format($item->total_cbm, 2) : '—' }}</td>
                                        <td>{{ $item->transport_per_unit ? number_format($item->transport_per_unit, 2) : '—' }}</td>
                                    @else
                                        <td>{{ $item->unit_price ? number_format($item->unit_price, 2) : '—' }}</td>
                                        <td>{{ $item->total_purchase_price ? number_format($item->total_purchase_price, 2) : '—' }}</td>
                                        <td>{{ number_format($item->transport ?? 0, 2) }}</td>
                                    @endif
                                    <td>{{ $item->unit_cost_arrival ? number_format($item->unit_cost_arrival, 2) : '—' }}</td>
                                    <td>{{ number_format($item->resolveMarketWholesalePrice(), 2) }}</td>
                                    <td class="{{ $marginPositive ? 'text-green-700' : 'text-red-600' }} font-medium">{{ $item->margin_amount !== null ? number_format($item->margin_amount, 2) : '—' }}</td>
                                    <td class="{{ $marginPositive ? 'text-green-700' : 'text-red-600' }}">{{ $item->margin_percent !== null ? number_format($item->margin_percent, 1).'%' : '—' }}</td>
                                    <td>{{ $item->actual_total_cost ? number_format($item->actual_total_cost, 2) : '—' }}</td>
                                    <td>{{ $item->expected_sales ? number_format($item->expected_sales, 2) : '—' }}</td>
                                    <td class="font-medium text-green-700">{{ $item->expected_margin ? number_format($item->expected_margin, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($series->isCalculated())
                            <tfoot class="font-semibold bg-stone-50 text-sm">
                                @if ($series->isImport())
                                    <tr><td colspan="13" class="text-right text-gray-500">Total purchase ({{ $series->currency }})</td><td colspan="3">{{ number_format($series->total_purchase_price, 2) }}</td></tr>
                                    <tr><td colspan="13" class="text-right text-gray-500">Total CBM</td><td colspan="3">{{ number_format($series->total_cbm, 2) }}</td></tr>
                                @endif
                                <tr><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-500">Total transport</td><td colspan="3">{{ number_format($series->total_transport_cost, 2) }}</td></tr>
                                <tr><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-500">Total actual cost</td><td colspan="3" class="text-orange-700">{{ number_format($series->total_actual_cost, 2) }}</td></tr>
                                <tr><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-500">Total expected sales</td><td colspan="3">{{ number_format($series->total_expected_sales, 2) }}</td></tr>
                                <tr class="bg-green-50"><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-600">Total expected margin</td><td colspan="3" class="text-green-700">{{ number_format($series->total_expected_margin, 2) }}</td></tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if ($series->canConfirm())
                @can('procurement.manage')
                    <div class="qs-action-bar">
                        <span class="text-sm text-gray-500">Review margins, then confirm to auto-approve this series.</span>
                        <form action="{{ route('quotation-series.confirm', $series) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="mi-btn-orange" onclick="return confirm('Confirm order? This will auto-approve the quotation series.')">
                                <i class="fas fa-check text-xs"></i> Confirm Order &amp; Approve
                            </button>
                        </form>
                    </div>
                @endcan
            @endif
        </div>
    </div>
@elseif ($series->status === 'quotation_draft')
    <div class="mi-card">
        <div class="qs-empty">
            <div class="qs-empty-icon"><i class="fas fa-arrow-left"></i></div>
            <p class="font-semibold text-gray-600">Order processing not started</p>
            <p class="text-sm text-gray-400 mt-1">Add products in the Quotation tab, then click <strong>Start Order Processing</strong>.</p>
        </div>
    </div>
@else
    <div class="mi-card">
        <div class="qs-empty">
            <div class="qs-empty-icon"><i class="fas fa-lock"></i></div>
            <p class="font-semibold text-gray-600">Order processing complete</p>
            <p class="text-sm text-gray-400 mt-1">This series has moved past the pricing phase. Check the Workflow tab for next steps.</p>
        </div>
    </div>
@endif

@if ($series->status === 'order_draft' && $series->items->isNotEmpty())
@push('scripts')
<script>
function orderProcessingPanel(pricesSaved, pricesExpandedInitially = true) {
    return {
        pricesSaved,
        pricesExpanded: pricesExpandedInitially,
        formDirty: false,

        init() {},

        togglePricesPanel() {
            this.pricesExpanded = !this.pricesExpanded;
        },

        onPricesFormSubmit() {
            this.pricesExpanded = false;
        },

        markDirty() {
            this.formDirty = true;
        },

        canRunCalculate() {
            return this.pricesSaved && !this.formDirty;
        },

        calculateHint() {
            if (!this.pricesSaved) {
                return 'Save supplier prices first';
            }
            if (this.formDirty) {
                return 'Save your price changes first';
            }
            return 'Calculate margins from saved prices';
        },
    };
}

function orderPriceLine(config) {
    return {
        isImport: config.isImport ?? false,
        quantity: config.quantity ?? 0,
        qtyPerPacket: config.qtyPerPacket ?? 1,
        overridePackets: config.overridePackets ?? false,
        displayPackets: 0,
        productMkt: config.productMkt ?? 0,
        overrideMkt: config.overrideMkt ?? false,
        displayMkt: 0,

        init() {
            this.syncPackets();
            this.syncMkt();
        },

        computedPackets() {
            const qpp = Math.max(0.01, parseFloat(this.qtyPerPacket) || 1);
            return Math.round((this.quantity / qpp) * 100) / 100;
        },

        syncPackets() {
            if (!this.isImport) {
                return;
            }
            this.displayPackets = this.overridePackets
                ? (config.savedPackets ?? this.computedPackets())
                : this.computedPackets();
        },

        syncMkt() {
            this.displayMkt = this.overrideMkt
                ? (config.savedMkt ?? this.productMkt)
                : this.productMkt;
        },

        onQtyPerPacketChange() {
            if (!this.overridePackets) {
                this.syncPackets();
            }
        },

        togglePacketsOverride(el) {
            if (!this.isImport) {
                return;
            }
            if (this.overridePackets) {
                this.overridePackets = false;
                this.syncPackets();
            } else {
                this.overridePackets = true;
                this.displayPackets = this.computedPackets();
            }
            this.markFormDirty(el);
        },

        toggleMktOverride(el) {
            if (this.overrideMkt) {
                this.overrideMkt = false;
                this.syncMkt();
            } else {
                this.overrideMkt = true;
                this.displayMkt = this.productMkt;
            }
            this.markFormDirty(el);
        },

        markFormDirty(el) {
            el.closest('form')?.dispatchEvent(new Event('change', { bubbles: true }));
        },
    };
}
</script>
@endpush
@endif
