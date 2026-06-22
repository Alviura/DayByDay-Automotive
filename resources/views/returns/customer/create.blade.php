<x-app-layout title="New Customer Return">

    @push('styles')
        <x-module.page-index-styles />
        @include('returns.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="customerReturnForm({
        searchUrl: @js(route('customer-returns.search-sales')),
        itemsUrl: @js(route('customer-returns.sale-items', ['sale' => '__ID__'])),
        prefillSaleId: @js($prefillSaleId),
    })">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-rotate-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">New Customer Return</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Search a completed sale, then specify items to return.</p>
                </div>
            </div>
            <a href="{{ route('customer-returns.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </div>

        <div class="mi-form-split">
            <form method="POST" action="{{ route('customer-returns.store') }}" class="mi-form-main space-y-5">
                @csrf
                <input type="hidden" name="sale_id" :value="saleId">

                <div class="mi-card p-5 space-y-4">
                    <p class="text-sm font-semibold text-gray-800">1. Select sale</p>

                    <div>
                        <label class="mi-field-label">Search receipt, fleet account, or plate</label>
                        <div class="flex gap-2">
                            <input type="text" class="mi-input flex-1" x-model="searchQuery" @input.debounce.300ms="searchSales()"
                                   placeholder="e.g. RCP-2026-0042, ABC Motors, KAA 123A">
                            <button type="button" class="mi-btn-ghost" @click="searchSales()" :disabled="searching">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div x-show="searchResults.length" x-cloak class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="sale in searchResults" :key="sale.id">
                            <button type="button" class="rt-sale-pick" :class="{ 'active': saleId == sale.id }" @click="pickSale(sale)">
                                <div class="flex justify-between gap-3">
                                    <div>
                                        <p class="font-mono font-bold text-sm" x-text="sale.receipt_number"></p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <span x-text="sale.customer"></span>
                                            <template x-if="sale.vehicle_plate">
                                                <span> · <span x-text="sale.vehicle_plate"></span></span>
                                            </template>
                                        </p>
                                    </div>
                                    <div class="text-right text-xs text-gray-500 shrink-0">
                                        <p x-text="sale.shop"></p>
                                        <p x-text="sale.sold_at"></p>
                                        <p class="font-semibold text-gray-700" x-text="'KES ' + sale.total.toLocaleString()"></p>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div x-show="selectedSale" x-cloak class="p-3 rounded-lg bg-orange-50 border border-orange-100 text-sm">
                        <p class="font-semibold text-orange-800">
                            Selected: <span class="font-mono" x-text="selectedSale?.receipt_number"></span>
                        </p>
                        <p class="text-orange-700 text-xs mt-0.5" x-text="selectedSale?.customer + (selectedSale?.vehicle_plate ? ' · ' + selectedSale.vehicle_plate : '')"></p>
                    </div>

                    @error('sale_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mi-card p-5 space-y-4" x-show="lines.length" x-cloak>
                    <p class="text-sm font-semibold text-gray-800">2. Return lines</p>

                    <template x-for="(line, index) in lines" :key="line.product_id">
                        <div class="mi-card p-4 border border-gray-100" x-show="line.returnable">
                            <input type="hidden" :name="`items[${index}][product_id]`" :value="line.product_id">
                            <div class="flex flex-wrap justify-between gap-2 mb-3">
                                <div>
                                    <p class="font-medium text-sm" x-text="line.part_number + ' — ' + line.name"></p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Sold <span x-text="line.sold_quantity"></span>
                                        <span x-text="line.unit ? ' ' + line.unit : ''"></span>
                                        · Already returned <span x-text="line.already_returned"></span>
                                        · <strong class="text-orange-700">Remaining <span x-text="line.remaining_quantity"></span></strong>
                                    </p>
                                </div>
                            </div>
                            <div class="mi-form-grid">
                                <div>
                                    <label class="mi-field-label text-gray-500">Return qty</label>
                                    <input type="number" step="0.01" min="0.01" :max="line.remaining_quantity"
                                           class="mi-input block w-full"
                                           :name="`items[${index}][quantity]`"
                                           x-model.number="line.quantity" required>
                                </div>
                                <div>
                                    <label class="mi-field-label text-gray-500">Unit price</label>
                                    <input type="number" step="0.01" class="mi-input block w-full"
                                           :name="`items[${index}][unit_price]`" x-model.number="line.unit_price">
                                </div>
                                <div>
                                    <label class="mi-field-label text-gray-500">Condition</label>
                                    <select :name="`items[${index}][condition]`" class="mi-select" x-model="line.condition"
                                            @change="line.restock = line.condition === 'good'">
                                        <option value="good">Good</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                </div>
                                <div class="flex items-end gap-4">
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" :name="`items[${index}][restock]`" value="1"
                                               x-model="line.restock" :disabled="line.condition === 'damaged'">
                                        Restock to shop
                                    </label>
                                </div>
                            </div>
                        </div>
                    </template>

                    <p x-show="lines.length && !lines.some(l => l.returnable)" class="text-sm text-amber-700 bg-amber-50 p-3 rounded-lg">
                        All items on this sale have already been fully returned.
                    </p>
                </div>

                <div class="mi-card p-5">
                    <label class="mi-field-label">Reason</label>
                    <input type="text" name="reason" class="mi-input block w-full" value="{{ old('reason') }}" required
                           placeholder="e.g. Wrong part supplied, defective item">
                    @error('reason')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('customer-returns.index') }}" class="mi-btn-ghost">Cancel</a>
                    <button type="submit" class="mi-btn-orange" :disabled="!canSubmit">
                        <i class="fas fa-save text-xs"></i> Save as Draft
                    </button>
                </div>
            </form>

            <x-return.form-guide type="customer" />
        </div>
    </div>

    @push('scripts')
    <script>
        function customerReturnForm(config) {
            return {
                searchUrl: config.searchUrl,
                itemsUrl: config.itemsUrl,
                searchQuery: '',
                searchResults: [],
                searching: false,
                saleId: config.prefillSaleId ? String(config.prefillSaleId) : '',
                selectedSale: null,
                lines: [],
                get canSubmit() {
                    return this.saleId && this.lines.some(l => l.returnable && l.quantity > 0);
                },
                init() {
                    this.searchSales();
                    if (this.saleId) {
                        this.loadSaleItems();
                    }
                },
                async searchSales() {
                    this.searching = true;
                    try {
                        const url = new URL(this.searchUrl, window.location.origin);
                        if (this.searchQuery) url.searchParams.set('q', this.searchQuery);
                        const res = await fetch(url);
                        this.searchResults = await res.json();
                    } finally {
                        this.searching = false;
                    }
                },
                pickSale(sale) {
                    this.saleId = String(sale.id);
                    this.selectedSale = sale;
                    this.loadSaleItems();
                },
                async loadSaleItems() {
                    if (!this.saleId) {
                        this.lines = [];
                        this.selectedSale = null;
                        return;
                    }
                    const url = this.itemsUrl.replace('__ID__', this.saleId);
                    const res = await fetch(url);
                    const data = await res.json();
                    if (data.sale) {
                        this.selectedSale = data.sale;
                    }
                    this.lines = (data.items || []).map(l => ({
                        ...l,
                        quantity: l.returnable ? Math.min(1, l.remaining_quantity) : 0,
                        condition: 'good',
                        restock: true,
                    }));
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
