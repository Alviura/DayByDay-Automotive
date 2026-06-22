<x-app-layout title="Point of Sale">
    @push('styles')<x-module.page-index-styles />@endpush

    <div
        class="mi-page"
        x-data="posScreen({
            shopId: {{ $shop->id }},
            saleId: {{ $resumeSale?->id ?? 'null' }},
            taxRate: {{ $taxRate }},
            searchUrl: '{{ route('sales.search') }}',
            cart: @js($resumeSale ? $resumeSale->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'part_number' => $i->product->part_number,
                'name' => $i->product->name,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'available' => null,
                'unit' => $i->product->unit?->abbreviation,
            ])->values() : []),
            customerName: @js($resumeSale?->customer_name ?? ''),
            customerPhone: @js($resumeSale?->customer_phone ?? ''),
            notes: @js($resumeSale?->notes ?? ''),
        })"
    >
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-cash-register"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Point of Sale</h1>
                    <p class="text-sm text-gray-500">{{ $shop->name }} ({{ $shop->code }})</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                @if ($shops->count() > 1 && ! auth()->user()->hasRole('Shop Manager'))
                    <form method="GET" action="{{ route('sales.pos') }}" class="flex gap-2">
                        <select name="shop_id" class="mi-select text-sm" onchange="this.form.submit()">
                            @foreach ($shops as $s)
                                <option value="{{ $s->id }}" @selected($s->id === $shop->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <a href="{{ route('sales.index') }}" class="mi-btn-ghost"><i class="fas fa-list text-xs"></i> Sales History</a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            {{-- Product search --}}
            <div class="xl:col-span-1 space-y-4">
                <div class="mi-card p-4">
                    <label class="mi-field-label">Search Products</label>
                    <input type="text" x-model="query" @input.debounce.300ms="search()" class="mi-input block w-full" placeholder="Part # or product name…" autofocus>
                    <div class="mt-3 space-y-2 max-h-[420px] overflow-y-auto">
                        <template x-for="product in results" :key="product.id">
                            <button type="button" @click="addProduct(product)"
                                    class="w-full text-left p-3 rounded-lg border border-gray-100 hover:border-orange-200 hover:bg-orange-50/50 transition">
                                <p class="font-medium text-sm text-gray-900" x-text="product.part_number"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="product.name"></p>
                                <div class="flex justify-between mt-1 text-xs">
                                    <span class="text-orange-600 font-semibold" x-text="formatPriceRange(product)"></span>
                                    <span class="text-gray-400" x-text="product.available + ' avail'"></span>
                                </div>
                            </button>
                        </template>
                        <p x-show="query && !results.length" class="text-sm text-gray-400 text-center py-4">No products found.</p>
                    </div>
                </div>

                @if ($heldSales->isNotEmpty())
                    <div class="mi-card p-4">
                        <p class="text-sm font-semibold mb-2">Held Sales</p>
                        <div class="space-y-1">
                            @foreach ($heldSales as $held)
                                <a href="{{ route('sales.pos', ['shop_id' => $shop->id, 'sale' => $held->id]) }}"
                                   class="block text-sm text-orange-600 hover:text-orange-700">
                                    {{ $held->receipt_number }} — {{ $held->items->count() }} items
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Cart --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="mi-card">
                    <div class="mi-card-head flex justify-between items-center">
                        <span class="text-sm font-semibold">Cart</span>
                        <button type="button" @click="clearCart()" class="text-xs text-gray-500 hover:text-red-600" x-show="cart.length">Clear</button>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Line</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, index) in cart" :key="line.product_id">
                                    <tr>
                                        <td>
                                            <p class="font-medium text-sm" x-text="line.part_number"></p>
                                            <p class="text-xs text-gray-500" x-text="line.name"></p>
                                        </td>
                                        <td><input type="number" step="0.01" min="0.01" class="mi-input w-20" x-model.number="line.quantity" @change="recalc()"></td>
                                        <td><input type="number" step="0.01" min="0" class="mi-input w-24" x-model.number="line.unit_price" @change="recalc()"></td>
                                        <td class="font-medium" x-text="formatMoney(lineTotal(line))"></td>
                                        <td><button type="button" @click="removeLine(index)" class="mi-action del"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </template>
                                <tr x-show="!cart.length">
                                    <td colspan="5" class="text-center py-10 text-gray-400">Search and add products to begin.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mi-card p-4 space-y-3">
                        <div><label class="mi-field-label">Customer Name</label><input type="text" x-model="customerName" class="mi-input block w-full"></div>
                        <div><label class="mi-field-label">Phone</label><input type="text" x-model="customerPhone" class="mi-input block w-full"></div>
                        <div><label class="mi-field-label">Notes</label><textarea x-model="notes" rows="2" class="mi-input block w-full"></textarea></div>
                    </div>
                    <div class="mi-card p-4">
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd x-text="formatMoney(totals.subtotal)"></dd></div>
                            <div class="flex justify-between" x-show="taxRate > 0"><dt class="text-gray-500">Tax</dt><dd x-text="formatMoney(totals.tax)"></dd></div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2"><dt>Total</dt><dd class="text-orange-600" x-text="formatMoney(totals.total)"></dd></div>
                        </dl>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <form method="POST" action="{{ route('sales.hold') }}" x-ref="holdForm">
                                @csrf
                                <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                                <template x-if="saleId"><input type="hidden" name="sale_id" :value="saleId"></template>
                                <template x-for="(line, i) in cart" :key="'h'+line.product_id">
                                    <div>
                                        <input type="hidden" :name="`items[${i}][product_id]`" :value="line.product_id">
                                        <input type="hidden" :name="`items[${i}][quantity]`" :value="line.quantity">
                                        <input type="hidden" :name="`items[${i}][unit_price]`" :value="line.unit_price">
                                    </div>
                                </template>
                                <input type="hidden" name="customer_name" :value="customerName">
                                <input type="hidden" name="customer_phone" :value="customerPhone">
                                <input type="hidden" name="notes" :value="notes">
                                <button type="button" @click="submitHold()" class="mi-btn-ghost" :disabled="!cart.length">
                                    <i class="fas fa-pause text-xs"></i> Hold
                                </button>
                            </form>
                            <button type="button" @click="paymentOpen = true" class="mi-btn-orange flex-1" :disabled="!cart.length">
                                <i class="fas fa-credit-card text-xs"></i> Pay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment modal --}}
        <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="mi-card w-full max-w-md p-6 space-y-4" @click.outside="paymentOpen = false">
                <h2 class="text-lg font-bold">Payment</h2>
                <p class="text-2xl font-bold text-orange-600" x-text="formatMoney(totals.total)"></p>

                <form method="POST" action="{{ route('sales.checkout') }}" x-ref="checkoutForm">
                    @csrf
                    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                    <template x-if="saleId"><input type="hidden" name="sale_id" :value="saleId"></template>
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
                        <div class="mi-form-grid mb-3">
                            <div>
                                <label class="mi-field-label">Method</label>
                                <select :name="`payments[${pi}][method]`" class="mi-select" x-model="payment.method">
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
                            <div class="mi-span-full" x-show="payment.method !== 'cash'">
                                <label class="mi-field-label">Reference</label>
                                <input type="text" class="mi-input block w-full" :name="`payments[${pi}][reference]`" x-model="payment.reference">
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="payments.push({method:'cash',amount:0,reference:''})" class="text-sm text-orange-600 mb-3">+ Add tender</button>

                    <div class="flex justify-between text-sm mb-4">
                        <span>Paid: <strong x-text="formatMoney(paidTotal())"></strong></span>
                        <span>Change: <strong x-text="formatMoney(Math.max(0, paidTotal() - totals.total))"></strong></span>
                    </div>

                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="paymentOpen = false" class="mi-btn-ghost">Cancel</button>
                        <button type="submit" class="mi-btn-orange" :disabled="paidTotal() < totals.total">Complete Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function posScreen(config) {
            return {
                shopId: config.shopId,
                saleId: config.saleId,
                taxRate: config.taxRate,
                searchUrl: config.searchUrl,
                query: '',
                results: [],
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

                async search() {
                    if (this.query.length < 2) { this.results = []; return; }
                    const res = await fetch(`${this.searchUrl}?shop_id=${this.shopId}&q=${encodeURIComponent(this.query)}`);
                    this.results = await res.json();
                },

                addProduct(product) {
                    const existing = this.cart.find(l => l.product_id === product.id);
                    if (existing) {
                        existing.quantity = parseFloat(existing.quantity) + 1;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            part_number: product.part_number,
                            name: product.name,
                            quantity: 1,
                            unit_price: parseFloat(product.max_selling_price),
                            min_selling_price: parseFloat(product.min_selling_price),
                            max_selling_price: parseFloat(product.max_selling_price),
                            available: product.available,
                            unit: product.unit,
                        });
                    }
                    this.recalc();
                    this.query = '';
                    this.results = [];
                },

                removeLine(index) {
                    this.cart.splice(index, 1);
                    this.recalc();
                },

                clearCart() {
                    this.cart = [];
                    this.saleId = null;
                    this.recalc();
                },

                lineTotal(line) {
                    return Math.max(0, line.quantity * line.unit_price);
                },

                recalc() {
                    const subtotal = this.cart.reduce((s, l) => s + (l.quantity * l.unit_price), 0);
                    const tax = Math.round(subtotal * this.taxRate * 100) / 100;
                    const total = Math.round((subtotal + tax) * 100) / 100;
                    this.totals = { subtotal, tax, total };
                },

                paidTotal() {
                    return this.payments.reduce((s, p) => s + (parseFloat(p.amount) || 0), 0);
                },

                formatMoney(amount) {
                    return 'KES ' + parseFloat(amount || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                formatPriceRange(product) {
                    const min = parseFloat(product.min_selling_price || 0);
                    const max = parseFloat(product.max_selling_price || 0);
                    if (min <= 0 && max <= 0) return '—';
                    if (min === max) return this.formatMoney(min);
                    return this.formatMoney(min) + ' – ' + this.formatMoney(max);
                },

                submitHold() {
                    if (!this.cart.length) return;
                    this.$refs.holdForm.submit();
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
