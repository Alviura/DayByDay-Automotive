<x-app-layout :title="'Checkout '.$sale->receipt_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('sales.partials.page-styles')
    @endpush

    <div class="mi-page pos-page space-y-5" x-data="deskCheckoutScreen({
        saleId: {{ $sale->id }},
        shopId: {{ $sale->shop_id }},
        taxRate: {{ $taxRate }},
        cart: @js($sale->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'part_number' => $i->product->part_number,
            'name' => $i->product->name,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'min_selling_price' => (float) $i->product->min_selling_price,
            'max_selling_price' => (float) $i->product->max_selling_price,
            'order_unit_label' => $i->product->orderUnitLabel(),
        ])->values()),
        customerName: @js($sale->customer_name ?? ''),
        customerPhone: @js($sale->customer_phone ?? ''),
        notes: @js($sale->notes ?? ''),
    })">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-cash-register"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 font-mono">{{ $sale->receipt_number }}</h1>
                        <span class="pos-desk-badge">At cash desk</span>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">
                        {{ $sale->shop?->name }}
                        @if ($sale->orderedBy)
                            · Ordered by <strong class="text-gray-700">{{ $sale->orderedBy->name }}</strong>
                            @if ($sale->submitted_at)
                                · {{ $sale->submitted_at->diffForHumans() }}
                            @endif
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sales.desk', ['shop_id' => $sale->shop_id]) }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i> Back to queue
                </a>
                <form action="{{ route('sales.abandon', $sale) }}" method="POST" class="inline"
                      data-confirm="Discard this order and release reserved stock?"
                      data-confirm-variant="danger"
                      data-confirm-label="Discard order">
                    @csrf
                    <button type="submit" class="mi-btn-ghost !text-red-600 !border-red-100 hover:!bg-red-50">
                        <i class="fas fa-trash-can text-xs"></i> Discard
                    </button>
                </form>
            </div>
        </div>

        @if ($sale->isCredit() && $sale->customerAccount)
            <div class="pos-customer-banner !bg-amber-50 !border-amber-200 !text-amber-900">
                <div><i class="fas fa-bus"></i> <strong>Fleet account:</strong> {{ $sale->customerAccount->name }}</div>
                @if ($sale->vehicle_plate)
                    <div><i class="fas fa-car"></i> <strong>Vehicle:</strong> {{ $sale->vehicle_plate }}</div>
                @endif
            </div>
        @elseif ($sale->customer_name || $sale->customer_phone || $sale->notes)
            <div class="pos-customer-banner">
                @if ($sale->customer_name || $sale->customer_phone)
                    <div>
                        <i class="fas fa-user"></i>
                        <strong>Customer:</strong>
                        {{ $sale->customer_name ?: 'Walk-in' }}
                        @if ($sale->customer_phone) · {{ $sale->customer_phone }} @endif
                    </div>
                @endif
                @if ($sale->notes)
                    <div class="w-full sm:w-auto">
                        <i class="fas fa-sticky-note"></i>
                        <strong>Attendant notes:</strong> {{ $sale->notes }}
                    </div>
                @endif
            </div>
        @endif

        <div class="pos-checkout-grid">
            {{-- Lines --}}
            <div class="pos-desk-main">
            <div class="pos-panel">
                <div class="pos-panel-head">
                    <div>
                        <span class="pos-panel-title"><i class="fas fa-sliders"></i> Negotiate prices</span>
                        <p class="text-xs text-gray-400 mt-0.5">Adjust unit price within each product's allowed range</p>
                    </div>
                    <span class="pos-cart-count" x-text="cart.length"></span>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="width:4rem">Qty</th>
                                <th style="width:8rem">Unit price</th>
                                <th style="width:9rem">Allowed range</th>
                                <th style="width:7rem">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, index) in cart" :key="line.product_id">
                                <tr>
                                    <td>
                                        <p class="font-semibold text-sm text-gray-900" x-text="line.part_number"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="line.name"></p>
                                    </td>
                                    <td class="font-bold text-gray-700">
                                        <span x-text="line.quantity"></span>
                                        <span class="text-xs font-normal text-gray-400" x-text="line.order_unit_label || 'PCS'"></span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               class="mi-input w-full !text-sm"
                                               :class="priceInRange(line) ? 'pos-price-ok' : 'pos-price-bad'"
                                               x-model.number="line.unit_price"
                                               @change="recalc()">
                                    </td>
                                    <td class="pos-price-hint whitespace-nowrap" x-text="formatPriceRange(line)"></td>
                                    <td class="font-bold text-orange-700" x-text="formatMoney(lineTotal(line))"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p x-show="!allPricesValid()" class="text-xs text-red-600 px-4 py-3 bg-red-50 border-t border-red-100">
                    <i class="fas fa-triangle-exclamation mr-1"></i>
                    One or more prices are outside the allowed range. Fix them before taking payment.
                </p>
            </div>
            </div>

            {{-- Totals + payment --}}
            <div class="pos-desk-side">
                <div class="pos-totals-panel">
                    <p class="pos-totals-label">{{ $sale->isCredit() ? 'Amount to account' : 'Amount due' }}</p>
                    <p class="pos-totals-amount" x-text="formatMoney(totals.total)"></p>
                    <div class="pos-totals-rows">
                        <div class="pos-totals-row"><span>Subtotal</span><strong x-text="formatMoney(totals.subtotal)"></strong></div>
                        <div class="pos-totals-row" x-show="taxRate > 0"><span>Tax</span><strong x-text="formatMoney(totals.tax)"></strong></div>
                        <div class="pos-totals-row"><span>Lines</span><strong x-text="cart.length"></strong></div>
                    </div>

                    @if ($sale->isCredit())
                        <form method="POST" action="{{ route('sales.issue-on-account', $sale) }}"
                              data-confirm="Issue these parts on account? Stock will be deducted and payment will be due on the monthly invoice."
                              data-confirm-variant="warning"
                              data-confirm-title="Issue on account?"
                              data-confirm-label="Issue on account">
                            @csrf
                            <button type="submit" class="pos-submit-btn" :disabled="!allPricesValid()">
                                <i class="fas fa-file-invoice"></i> Issue on account
                            </button>
                        </form>
                    @else
                        <button type="button" @click="paymentOpen = true" class="pos-submit-btn"
                                :disabled="!allPricesValid()">
                            <i class="fas fa-credit-card"></i> Take payment
                        </button>
                    @endif
                </div>

                <div class="mi-guide-note mi-guide-note-amber !m-0 !rounded-xl">
                    <i class="fas fa-info-circle"></i>
                    @if ($sale->isCredit())
                        <p class="text-sm">No payment now — this sale will appear on the customer's monthly invoice for settlement.</p>
                    @else
                        <p class="text-sm">Prices must stay within each product's min–max selling range before you can complete the sale.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment modal (retail only) --}}
        @if (! $sale->isCredit())
        <div x-show="paymentOpen" x-cloak class="pos-payment-overlay">
            <div class="pos-payment-modal" @click.outside="paymentOpen = false">
                <div class="pos-payment-head">
                    <h2><i class="fas fa-wallet mr-2"></i>Take payment</h2>
                    <p class="pos-payment-amount" x-text="formatMoney(totals.total)"></p>
                </div>
                <div class="pos-payment-body">
                    <form method="POST" action="{{ route('sales.checkout') }}" x-ref="checkoutForm">
                        @csrf
                        <input type="hidden" name="shop_id" value="{{ $sale->shop_id }}">
                        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                        <template x-for="(line, i) in cart" :key="'c'+line.product_id">
                            <div>
                                <input type="hidden" :name="`items[${i}][product_id]`" :value="line.product_id">
                                <input type="hidden" :name="`items[${i}][quantity]`" :value="line.quantity">
                                <input type="hidden" :name="`items[${i}][unit_price]`" :value="line.unit_price">
                            </div>
                        </template>
                        <input type="hidden" name="customer_name" :value="customerName">
                        <input type="hidden" name="customer_phone" :value="customerPhone">
                        <input type="hidden" name="notes" :value="notes">

                        <template x-for="(payment, pi) in payments" :key="pi">
                            <div class="pos-tender-row">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="mi-field-label">Method</label>
                                        <select :name="`payments[${pi}][method]`" class="mi-select w-full" x-model="payment.method">
                                            @foreach ($paymentMethods as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mi-field-label">Amount</label>
                                        <input type="number" step="0.01" min="0" class="mi-input block w-full"
                                               :name="`payments[${pi}][amount]`" x-model.number="payment.amount">
                                    </div>
                                </div>
                                <div class="mt-3" x-show="payment.method !== 'cash'">
                                    <label class="mi-field-label">Reference</label>
                                    <input type="text" class="mi-input block w-full" :name="`payments[${pi}][reference]`" x-model="payment.reference"
                                           placeholder="Transaction ID, last 4 digits…">
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="payments.push({method:'cash',amount:0,reference:''})"
                                class="text-sm text-orange-600 font-medium mb-3 hover:text-orange-700">
                            <i class="fas fa-plus mr-1"></i> Add another tender
                        </button>

                        <div class="pos-change-bar">
                            <span>Paid: <strong x-text="formatMoney(paidTotal())"></strong></span>
                            <span>Change: <strong x-text="formatMoney(Math.max(0, paidTotal() - totals.total))"></strong></span>
                        </div>

                        <div class="flex gap-2 justify-end pt-2">
                            <button type="button" @click="paymentOpen = false" class="mi-btn-ghost">Cancel</button>
                            <button type="submit" class="mi-btn-orange" :disabled="paidTotal() < totals.total">
                                <i class="fas fa-check text-xs"></i> Complete sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function deskCheckoutScreen(config) {
            return {
                saleId: config.saleId,
                shopId: config.shopId,
                taxRate: config.taxRate,
                cart: config.cart || [],
                customerName: config.customerName || '',
                customerPhone: config.customerPhone || '',
                notes: config.notes || '',
                paymentOpen: false,
                payments: [{ method: 'cash', amount: 0, reference: '' }],
                totals: { subtotal: 0, tax: 0, total: 0 },

                init() {
                    this.recalc();
                    this.$watch('paymentOpen', (open) => {
                        if (open) {
                            this.payments = [{ method: 'cash', amount: this.totals.total, reference: '' }];
                        }
                    });
                },

                lineTotal(line) {
                    return Math.max(0, line.quantity * line.unit_price);
                },

                priceInRange(line) {
                    const price = parseFloat(line.unit_price);
                    const min = parseFloat(line.min_selling_price || 0);
                    const max = parseFloat(line.max_selling_price || 0);
                    if (min <= 0 && max <= 0) return true;
                    const floor = min > 0 ? min : 0;
                    const ceiling = max > 0 ? max : min;
                    return price >= floor && price <= ceiling;
                },

                allPricesValid() {
                    return this.cart.every(l => this.priceInRange(l));
                },

                recalc() {
                    const subtotal = this.cart.reduce((s, l) => s + this.lineTotal(l), 0);
                    const tax = Math.round(subtotal * this.taxRate * 100) / 100;
                    this.totals = { subtotal, tax, total: Math.round((subtotal + tax) * 100) / 100 };
                },

                paidTotal() {
                    return this.payments.reduce((s, p) => s + (parseFloat(p.amount) || 0), 0);
                },

                formatMoney(amount) {
                    return 'KES ' + parseFloat(amount || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                formatPriceRange(line) {
                    const min = parseFloat(line.min_selling_price || 0);
                    const max = parseFloat(line.max_selling_price || 0);
                    if (min <= 0 && max <= 0) return '—';
                    if (min === max) return this.formatMoney(min);
                    return this.formatMoney(min) + ' – ' + this.formatMoney(max);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
