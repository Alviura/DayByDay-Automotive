<div class="qs-grid-2">
    @if ($series->canBulkAddItems())
        @can('procurement.manage')
            <div class="mi-card qs-picker-card"
                 x-data="quotationProductPicker({
                     searchUrl: @js(route('quotation-series.products.search', $series)),
                 })">
                <div class="mi-card-head">
                    <div>
                        <div class="qs-section-title"><i class="fas fa-cart-plus"></i> Add Products</div>
                        <p class="qs-section-sub">Search and add products to this quotation</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('quotation-series.items.bulk', $series) }}" @submit="onSubmit($event)">
                    @csrf

                    {{-- Search --}}
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search"
                                   x-model="query"
                                   @input.debounce.300ms="search()"
                                   placeholder="Search part number, name, make… (min 2 chars)"
                                   class="mi-input"
                                   autocomplete="off">
                        </div>
                        <p class="text-xs text-gray-400 mt-2" x-show="searching" x-cloak>Searching…</p>
                    </div>

                    <div class="qs-search-results">
                        <template x-for="product in results" :key="product.id">
                            <button type="button"
                                    class="qs-search-result"
                                    :class="{ 'is-added': isInBasket(product.id) }"
                                    @click="addProduct(product)"
                                    :disabled="isInBasket(product.id)">
                                <div class="qs-product-icon"><i class="fas fa-cog"></i></div>
                                <div class="min-w-0 flex-1 text-left">
                                    <p class="text-sm font-semibold text-gray-900" x-text="product.part_number"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="product.name"></p>
                                    <p class="text-xs text-gray-400" x-text="metaLabel(product)"></p>
                                </div>
                                <span class="qs-search-add" x-show="!isInBasket(product.id)"><i class="fas fa-plus"></i></span>
                                <span class="qs-search-added" x-show="isInBasket(product.id)" x-cloak>Added</span>
                            </button>
                        </template>
                        <div x-show="query.length >= 2 && !searching && !results.length" class="qs-empty py-8" x-cloak>
                            <p class="text-sm text-gray-400">No products found.</p>
                        </div>
                        <div x-show="query.length < 2 && !basket.length" class="qs-empty py-8">
                            <div class="qs-empty-icon"><i class="fas fa-magnifying-glass"></i></div>
                            <p class="text-sm text-gray-500">Type at least 2 characters to search</p>
                        </div>
                    </div>

                    {{-- Selection basket --}}
                    <div class="qs-basket" x-show="basket.length" x-cloak>
                        <div class="qs-picker-toolbar">
                            <span class="qs-section-title text-xs"><i class="fas fa-clipboard-list"></i> Selected</span>
                            <button type="button" class="mi-btn-ghost text-xs" @click="clearBasket()">Clear all</button>
                        </div>
                        <div class="qs-basket-list">
                            <template x-for="(line, index) in basket" :key="line.product_id">
                                <div class="qs-basket-row">
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="line.product_id">
                                    <div class="qs-product-icon"><i class="fas fa-cog"></i></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900" x-text="line.part_number"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="line.name"></p>
                                    </div>
                                    <div class="w-24">
                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               :name="`items[${index}][quantity]`"
                                               x-model.number="line.quantity"
                                               class="mi-input w-full text-sm"
                                               required>
                                    </div>
                                    <button type="button" class="mi-action del" @click="removeFromBasket(index)" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="qs-action-bar">
                        <span class="text-xs text-gray-400">
                            <span x-text="basket.length"></span> selected
                        </span>
                        <button type="submit" class="mi-btn-orange" :class="{ 'opacity-50 cursor-not-allowed': !basket.length }">
                            <i class="fas fa-plus text-xs"></i> Add Selected
                        </button>
                    </div>
                </form>
            </div>
        @endcan
    @endif

    <div class="mi-card {{ $series->canBulkAddItems() ? '' : 'col-span-full' }}">
        <div class="mi-card-head">
            <div class="qs-section-head w-full">
                <div>
                    <div class="qs-section-title"><i class="fas fa-list"></i> Quotation List</div>
                    <p class="qs-section-sub">{{ $series->items->count() }} line {{ str('item')->plural($series->items->count()) }}</p>
                </div>
                @if ($series->canExportQuotation() && $series->items->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('quotation-series.export', [$series, 'csv']) }}" class="mi-btn-ghost text-xs"><i class="fas fa-file-csv"></i> CSV</a>
                        <a href="{{ route('quotation-series.export', [$series, 'print']) }}" target="_blank" class="mi-btn-ghost text-xs"><i class="fas fa-print"></i> Print</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="mi-table-wrap overflow-x-auto">
            <table class="mi-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Part Number</th>
                        <th>Product</th>
                        <th>Make / Vehicle</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        @if ($series->canBulkAddItems())<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($series->items as $index => $item)
                        <tr>
                            <td class="text-gray-400 font-medium">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('products.show', $item->product) }}" class="font-medium text-orange-600 hover:text-orange-700">{{ $item->product->part_number }}</a>
                            </td>
                            <td>{{ $item->product->productName?->name ?? $item->product->name }}</td>
                            <td class="text-sm text-gray-500">
                                {{ $item->product->vehicleMake?->name ?? '—' }}
                                @if ($item->product->vehicleModel?->name)
                                    <span class="text-gray-300">·</span> {{ $item->product->vehicleModel->name }}
                                @endif
                            </td>
                            <td>{{ $item->product->unit?->abbreviation ?? $item->product->unit?->name ?? '—' }}</td>
                            <td><span class="mi-cat-badge">{{ number_format($item->quantity, 0) }}</span></td>
                            <td class="text-gray-400">{{ $item->hasPrice() ? number_format((float) ($item->unit_price_foreign ?? $item->unit_price), 2) : '—' }}</td>
                            @if ($series->canBulkAddItems())
                                <td>
                                    @can('procurement.manage')
                                        <form action="{{ route('quotation-series.items.destroy', [$series, $item]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this line?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="mi-action del" title="Remove"><i class="fas fa-trash-can"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $series->canBulkAddItems() ? 8 : 7 }}">
                                <div class="qs-empty">
                                    <div class="qs-empty-icon"><i class="fas fa-box-open"></i></div>
                                    <p class="font-semibold text-gray-600">No products in quotation yet</p>
                                    <p class="text-sm text-gray-400 mt-1">Search and add products using the picker.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($series->canProceedToOrder())
            @can('procurement.manage')
                <div class="qs-action-bar">
                    <span class="text-sm text-gray-500">{{ $series->items->count() }} products ready for pricing</span>
                    <form action="{{ route('quotation-series.proceed', $series) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="mi-btn-orange" onclick="return confirm('Proceed to order processing? Supplier prices will be entered next.')">
                            Start Order Processing <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </form>
                </div>
            @endcan
        @endif
    </div>
</div>

@push('scripts')
<script>
function quotationProductPicker(config) {
    return {
        searchUrl: config.searchUrl,
        query: '',
        results: [],
        basket: [],
        searching: false,

        async search() {
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }

            this.searching = true;
            try {
                const res = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.query.trim())}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) {
                    this.results = [];
                    return;
                }
                const data = await res.json();
                this.results = Array.isArray(data) ? data : [];
            } catch (e) {
                this.results = [];
            } finally {
                this.searching = false;
            }
        },

        metaLabel(product) {
            return [product.make, product.vehicle, product.unit].filter(Boolean).join(' · ') || '—';
        },

        isInBasket(productId) {
            return this.basket.some(line => line.product_id === productId);
        },

        addProduct(product) {
            if (this.isInBasket(product.id)) {
                return;
            }
            this.basket.push({
                product_id: product.id,
                part_number: product.part_number,
                name: product.name,
                quantity: 1,
            });
        },

        removeFromBasket(index) {
            this.basket.splice(index, 1);
        },

        clearBasket() {
            this.basket = [];
        },

        onSubmit(event) {
            if (!this.basket.length) {
                event.preventDefault();
                return;
            }

            this.basket.forEach(line => {
                if (!line.quantity || line.quantity <= 0) {
                    line.quantity = 1;
                }
            });
        },
    };
}
</script>
@endpush
