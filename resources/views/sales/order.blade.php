<x-app-layout title="Order Entry">

    @push('styles')
        <x-module.page-index-styles />
        @include('sales.partials.page-styles')
    @endpush

    <div class="mi-page pos-page space-y-5" x-data="orderEntryScreen({
        shopId: {{ $shop->id }},
        searchUrl: '{{ route('sales.search') }}',
        taxRate: {{ config('sales.tax_rate', 0) }},
    })">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Order Entry</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Build the customer's order and send them to the cash desk.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                @can('sales.create')
                    <a href="{{ route('sales.desk', ['shop_id' => $shop->id]) }}" class="mi-btn-ghost">
                        <i class="fas fa-cash-register text-xs"></i> Cash Desk
                    </a>
                @endcan
                <a href="{{ route('sales.index', ['shop_id' => $shop->id, 'status' => 'held']) }}" class="mi-btn-ghost">
                    <i class="fas fa-hourglass-half text-xs"></i> At Desk
                </a>
            </div>
        </div>

        {{-- Shop bar --}}
        <div class="pos-shop-bar">
            <i class="fas fa-store"></i>
            <span class="text-sm text-gray-600">Serving at</span>
            @if ($shops->count() > 1 && ! auth()->user()->shop_id)
                <form method="GET" action="{{ route('sales.order') }}" class="inline-flex">
                    <select name="shop_id" class="mi-select text-sm !py-1 !px-2 font-semibold" onchange="this.form.submit()">
                        @foreach ($shops as $s)
                            <option value="{{ $s->id }}" @selected($s->id === $shop->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <strong>{{ $shop->name }}</strong>
            @endif
        </div>

        {{-- Workflow --}}
        <div class="pos-workflow">
            <div class="pos-step" :class="{ 'active': !cart.length, 'done': cart.length }">
                <span class="pos-step-num" x-text="cart.length ? '✓' : '1'"></span>
                <div>
                    <p class="pos-step-title">Search products</p>
                    <p class="pos-step-desc">Find parts by number or name</p>
                </div>
            </div>
            <div class="pos-step" :class="{ 'active': cart.length && !customerName && !notes, 'done': cart.length }">
                <span class="pos-step-num" x-text="cart.length ? '✓' : '2'"></span>
                <div>
                    <p class="pos-step-title">Build order</p>
                    <p class="pos-step-desc" x-text="cart.length ? cart.length + ' line(s) in cart' : 'Add items to the cart'"></p>
                </div>
            </div>
            <div class="pos-step" :class="{ 'active': cart.length }">
                <span class="pos-step-num">3</span>
                <div>
                    <p class="pos-step-title">Send to desk</p>
                    <p class="pos-step-desc">Customer pays at cash desk</p>
                </div>
            </div>
        </div>

        <div class="pos-main-grid">
            {{-- Main: search + cart + customer --}}
            <div class="pos-order-main">
                <div class="pos-panel pos-search-inline">
                    <div class="pos-panel-head">
                        <span class="pos-panel-title"><i class="fas fa-magnifying-glass"></i> Product search</span>
                    </div>
                    <div class="pos-panel-body">
                        <div class="pos-search-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" x-model="query" @input.debounce.300ms="search()"
                                   class="pos-search-input" placeholder="Part # or product name…" autofocus>
                        </div>
                        <div class="pos-results" x-show="query.length > 0">
                            <template x-for="product in results" :key="product.id">
                                <button type="button" @click="addProduct(product)" class="pos-product-card">
                                    <div class="pos-product-top">
                                        <span class="pos-product-sku" x-text="product.part_number"></span>
                                        <span class="pos-stock-badge"
                                              :class="product.available <= 0 ? 'pos-stock-out' : (product.available <= 3 ? 'pos-stock-low' : 'pos-stock-ok')"
                                              x-text="product.available + ' in stock'"></span>
                                    </div>
                                    <p class="pos-product-name" x-text="product.name"></p>
                                    <div class="pos-product-foot">
                                        <span class="text-gray-400">Tap to add</span>
                                        <span class="pos-product-price" x-text="formatPriceRange(product)"></span>
                                    </div>
                                </button>
                            </template>
                            <p x-show="query.length >= 2 && !results.length" class="text-sm text-gray-400 text-center py-6 col-span-full">
                                <i class="fas fa-box-open text-gray-300 text-lg block mb-2"></i>
                                No products found for "<span x-text="query"></span>"
                            </p>
                            <p x-show="query.length > 0 && query.length < 2" class="text-xs text-gray-400 text-center py-4 col-span-full">
                                Type at least 2 characters…
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pos-panel">
                    <div class="pos-panel-head">
                        <span class="pos-panel-title">
                            <i class="fas fa-cart-shopping"></i> Customer order
                            <span class="pos-cart-count" x-show="cart.length" x-text="cart.length"></span>
                        </span>
                        <button type="button" @click="clearCart()" class="text-xs text-gray-400 hover:text-red-600 font-medium"
                                x-show="cart.length">
                            <i class="fas fa-trash-can mr-1"></i> Clear all
                        </button>
                    </div>
                    <div class="mi-table-wrap" x-show="cart.length">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th style="width:7.5rem">Qty</th>
                                    <th style="width:6.5rem" x-text="saleType === 'credit' ? 'Fleet price' : 'List price'"></th>
                                    <th style="width:6.5rem">Est. line</th>
                                    <th style="width:2.5rem"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, index) in cart" :key="line.product_id">
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-sm text-gray-900" x-text="line.part_number"></p>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="line.name"></p>
                                        </td>
                                        <td>
                                            <div class="pos-qty-wrap">
                                                <button type="button" class="pos-qty-btn" @click="adjustQty(index, -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" step="0.01" min="0.01" class="pos-qty-input"
                                                       x-model.number="line.quantity" @change="recalc()">
                                                <button type="button" class="pos-qty-btn" @click="adjustQty(index, 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-sm text-gray-600" x-text="formatMoney(line.list_price)"></td>
                                        <td class="font-bold text-sm text-orange-700" x-text="formatMoney(line.quantity * line.list_price)"></td>
                                        <td>
                                            <button type="button" @click="removeLine(index)" class="mi-action del" title="Remove">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="pos-cart-empty" x-show="!cart.length">
                        <div class="pos-cart-empty-icon"><i class="fas fa-cart-shopping"></i></div>
                        <p class="font-semibold text-gray-600">Cart is empty</p>
                        <p class="text-sm text-gray-400 mt-1">Search and add products for the customer.</p>
                    </div>
                </div>

                <div class="pos-panel" x-show="cart.length">
                    <div class="pos-panel-head">
                        <span class="pos-panel-title"><i class="fas fa-user"></i> Customer details</span>
                        <span class="pos-mode-badge" :class="saleType === 'credit' ? 'pos-mode-badge-fleet' : 'pos-mode-badge-retail'"
                              x-text="saleType === 'credit' ? 'Fleet mode' : 'Retail mode'"></span>
                    </div>
                    <div class="pos-panel-body">
                        {{-- Mode slider --}}
                        <div class="pos-mode-toggle mb-4">
                            <span class="pos-mode-label" :class="saleType === 'retail' ? 'active active-retail' : ''">
                                <i class="fas fa-cash-register"></i> Retail
                            </span>
                            <label class="pos-mode-switch" title="Toggle between Retail and Fleet">
                                <input type="checkbox" :checked="saleType === 'credit'"
                                       @change="toggleSaleType($event.target.checked)">
                                <span class="pos-mode-track"></span>
                                <span class="pos-mode-thumb"></span>
                            </label>
                            <span class="pos-mode-label" :class="saleType === 'credit' ? 'active active-fleet' : ''">
                                <i class="fas fa-bus"></i> Fleet
                            </span>
                        </div>

                        <div x-show="saleType === 'credit'" x-transition>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="mi-field-label pos-field-required">Customer account</label>
                                    <select x-model="customerAccountId" class="mi-select block w-full"
                                            :class="{ 'pos-input-invalid': fleetErrors.account }">
                                        <option value="">Select fleet account…</option>
                                        @foreach ($creditAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}@if($account->contact_name) — {{ $account->contact_name }}@endif</option>
                                        @endforeach
                                    </select>
                                    <p x-show="fleetErrors.account" class="text-xs text-red-600 mt-1">Please select a customer account.</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mi-field-label pos-field-required">Vehicle plate</label>
                                    <input type="text" x-model="vehiclePlate" @input="vehiclePlate = vehiclePlate.toUpperCase()"
                                           class="mi-input block w-full uppercase"
                                           :class="{ 'pos-input-invalid': fleetErrors.plate }"
                                           placeholder="e.g. KCA 123A">
                                    <p x-show="fleetErrors.plate" class="text-xs text-red-600 mt-1">Vehicle plate is required for fleet sales.</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="saleType === 'retail'" x-transition>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="mi-field-label">Customer name</label>
                                    <input type="text" x-model="customerName" class="mi-input block w-full" placeholder="Walk-in or name">
                                </div>
                                <div>
                                    <label class="mi-field-label">Phone</label>
                                    <input type="text" x-model="customerPhone" class="mi-input block w-full" placeholder="07xx xxx xxx">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mi-field-label">Notes for cashier</label>
                            <textarea x-model="notes" rows="2" class="mi-input block w-full resize-y"
                                      placeholder="Price expectations, special requests…"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: totals + guide --}}
            <div class="pos-order-side">
                <div class="pos-totals-panel">
                    <p class="pos-totals-label">Estimated total</p>
                    <p class="pos-totals-amount" x-text="formatMoney(totals.total)"></p>
                    <div class="pos-totals-rows">
                        <div class="pos-totals-row"><span>Subtotal</span><strong x-text="formatMoney(totals.subtotal)"></strong></div>
                        <div class="pos-totals-row" x-show="taxRate > 0"><span>Tax</span><strong x-text="formatMoney(totals.tax)"></strong></div>
                        <div class="pos-totals-row"><span>Lines</span><strong x-text="cart.length"></strong></div>
                    </div>
                    <p class="pos-price-hint mt-3 !text-gray-400" x-show="saleType === 'retail'">
                        List-price estimate. Final amount is negotiated at the cash desk.
                    </p>
                    <p class="pos-price-hint mt-3 !text-gray-400" x-show="saleType === 'credit'">
                        Fleet pricing uses minimum selling price. Payment on monthly invoice.
                    </p>
                    <form method="POST" action="{{ route('sales.hold') }}" x-ref="submitForm">
                        @csrf
                        <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                        <input type="hidden" name="sale_type" :value="saleType">
                        <input type="hidden" name="customer_account_id" :value="saleType === 'credit' ? customerAccountId : ''">
                        <input type="hidden" name="vehicle_plate" :value="vehiclePlate">
                        <template x-for="(line, i) in cart" :key="'s'+line.product_id">
                            <div>
                                <input type="hidden" :name="`items[${i}][product_id]`" :value="line.product_id">
                                <input type="hidden" :name="`items[${i}][quantity]`" :value="line.quantity">
                            </div>
                        </template>
                        <input type="hidden" name="customer_name" :value="customerName">
                        <input type="hidden" name="customer_phone" :value="customerPhone">
                        <input type="hidden" name="notes" :value="notes">
                        <button type="button" @click="submitOrder()" class="pos-submit-btn" :disabled="!cart.length">
                            <i class="fas fa-arrow-right"></i>
                            <span x-text="saleType === 'credit' ? 'Send fleet order to desk' : 'Send to cash desk'"></span>
                        </button>
                    </form>
                </div>

                @include('sales.partials.order-guide')
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function orderEntryScreen(config) {
            return {
                shopId: config.shopId,
                searchUrl: config.searchUrl,
                taxRate: config.taxRate,
                query: '',
                results: [],
                cart: [],
                customerName: '',
                customerPhone: '',
                notes: '',
                saleType: 'retail',
                customerAccountId: '',
                vehiclePlate: '',
                fleetErrors: { account: false, plate: false },
                totals: { subtotal: 0, tax: 0, total: 0 },

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
                        const minPrice = parseFloat(product.min_selling_price) || 0;
                        const maxPrice = parseFloat(product.max_selling_price) || 0;
                        this.cart.push({
                            product_id: product.id,
                            part_number: product.part_number,
                            name: product.name,
                            quantity: 1,
                            min_price: minPrice,
                            max_price: maxPrice,
                            list_price: this.listPriceFor(minPrice, maxPrice),
                        });
                    }
                    this.recalc();
                    this.query = '';
                    this.results = [];
                },

                listPriceFor(minPrice, maxPrice) {
                    if (this.saleType === 'credit') {
                        return minPrice > 0 ? minPrice : (maxPrice || 0);
                    }
                    return maxPrice > 0 ? maxPrice : (minPrice || 0);
                },

                applySaleTypePricing() {
                    this.cart.forEach(line => {
                        line.list_price = this.listPriceFor(line.min_price, line.max_price);
                    });
                    this.recalc();
                },

                toggleSaleType(isFleet) {
                    this.saleType = isFleet ? 'credit' : 'retail';
                    this.fleetErrors = { account: false, plate: false };
                    this.applySaleTypePricing();
                },

                adjustQty(index, delta) {
                    const line = this.cart[index];
                    const next = parseFloat(line.quantity) + delta;
                    if (next < 0.01) {
                        this.removeLine(index);
                        return;
                    }
                    line.quantity = next;
                    this.recalc();
                },

                removeLine(index) {
                    this.cart.splice(index, 1);
                    this.recalc();
                },

                async clearCart() {
                    if (!this.cart.length || await window.appConfirm({
                        message: 'Clear all items from the cart?',
                        variant: 'warning',
                        confirmLabel: 'Clear cart',
                    })) {
                        this.cart = [];
                        this.recalc();
                    }
                },

                recalc() {
                    const subtotal = this.cart.reduce((s, l) => s + (l.quantity * l.list_price), 0);
                    const tax = Math.round(subtotal * this.taxRate * 100) / 100;
                    this.totals = { subtotal, tax, total: Math.round((subtotal + tax) * 100) / 100 };
                },

                submitOrder() {
                    if (!this.cart.length) return;
                    this.fleetErrors = { account: false, plate: false };

                    if (this.saleType === 'credit') {
                        if (!this.customerAccountId) this.fleetErrors.account = true;
                        if (!this.vehiclePlate.trim()) this.fleetErrors.plate = true;
                        if (this.fleetErrors.account || this.fleetErrors.plate) return;
                    }

                    this.$refs.submitForm.submit();
                },

                formatMoney(amount) {
                    return 'KES ' + parseFloat(amount || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                formatPriceRange(product) {
                    const min = parseFloat(product.min_selling_price || 0);
                    const max = parseFloat(product.max_selling_price || 0);
                    if (this.saleType === 'credit') {
                        if (min <= 0 && max <= 0) return '—';
                        return this.formatMoney(min > 0 ? min : max);
                    }
                    if (min <= 0 && max <= 0) return '—';
                    if (min === max) return this.formatMoney(min);
                    return this.formatMoney(min) + ' – ' + this.formatMoney(max);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
