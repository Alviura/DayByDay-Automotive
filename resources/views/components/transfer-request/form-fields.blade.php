@props([
    'warehouses' => collect(),
    'shops' => collect(),
    'destinationShops' => collect(),
    'products' => collect(),
    'prefill' => [],
    'lockedShop' => null,
])

@php
    $defaultItems = [['product_id' => (string) ($prefill['product_id'] ?? ''), 'requested_quantity' => '']];
    if (old('items')) {
        $defaultItems = old('items');
    }
    $allShops = $destinationShops->isNotEmpty() ? $destinationShops : $shops;
@endphp

<div
    x-data="{
        type: @js(old('type', $prefill['type'] ?? 'warehouse_to_shop')),
        sourceId: @js((string) old('source_id', $prefill['source_id'] ?? '')),
        destinationId: @js((string) old('destination_id', $prefill['destination_id'] ?? ($lockedShop?->id ?? ''))),
        rows: @js($defaultItems),
        availability: {},
        loadingAvailability: false,
        availabilityUrl: @js(route('transfer-requests.availability')),
        allShops: @js($allShops->map(fn ($s) => ['id' => (string) $s->id, 'name' => $s->name])->values()),
        lockedDestination: @js((bool) $lockedShop),
        addRow() { this.rows.push({ product_id: '', requested_quantity: '' }); },
        removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
        sourceShops() {
            return this.allShops.filter(s => s.id !== this.destinationId);
        },
        async refreshAvailability() {
            const productIds = this.rows.map(r => r.product_id).filter(Boolean);
            if (!this.sourceId || productIds.length === 0) {
                this.availability = {};
                return;
            }
            this.loadingAvailability = true;
            try {
                const params = new URLSearchParams({ type: this.type, source_id: this.sourceId });
                productIds.forEach(id => params.append('product_ids[]', id));
                const res = await fetch(this.availabilityUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                this.availability = data.availability ?? {};
            } catch (e) {
                this.availability = {};
            } finally {
                this.loadingAvailability = false;
            }
        },
        availableFor(productId) {
            return this.availability[productId]?.available ?? null;
        },
    }"
    x-init="
        $watch('type', () => refreshAvailability());
        $watch('sourceId', () => refreshAvailability());
        $watch('destinationId', (id) => {
            if (type === 'inter_shop' && id && sourceId === id) {
                const alt = sourceShops().find(s => s.id !== id);
                if (alt) sourceId = alt.id;
            }
            refreshAvailability();
        });
        $watch('rows', () => refreshAvailability(), { deep: true });
        refreshAvailability();
    "
    class="space-y-6"
>
    <div class="mi-form-grid">
        <div>
            <label class="mi-field-label"><i class="fas fa-route"></i> Request Type</label>
            <select name="type" class="mi-select" x-model="type" required>
                <option value="warehouse_to_shop">Request from Warehouse</option>
                <option value="inter_shop">Request from Another Shop</option>
            </select>
        </div>

        @if ($lockedShop)
            <div>
                <label class="mi-field-label"><i class="fas fa-store"></i> Destination Shop</label>
                <input type="hidden" name="destination_id" value="{{ $lockedShop->id }}" x-model="destinationId">
                <div class="mi-input block w-full bg-gray-50 text-gray-700 cursor-not-allowed">{{ $lockedShop->name }}</div>
                <p class="mi-field-hint mt-1">Stock will be requested for your shop.</p>
            </div>
        @else
            <div>
                <label class="mi-field-label"><i class="fas fa-store"></i> Destination Shop</label>
                <select name="destination_id" class="mi-select" x-model="destinationId" required>
                    @foreach ($allShops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
                <p class="mi-field-hint mt-1">Select which shop this request is for.</p>
            </div>
        @endif

        <div x-show="type === 'warehouse_to_shop'" x-cloak>
            <label class="mi-field-label"><i class="fas fa-warehouse"></i> Source Warehouse</label>
            <select name="source_id" class="mi-select" x-model="sourceId" required>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="type === 'inter_shop'" x-cloak>
            <label class="mi-field-label"><i class="fas fa-store"></i> Source Shop</label>
            <select name="source_id" class="mi-select" x-model="sourceId" required>
                <template x-for="shop in sourceShops()" :key="shop.id">
                    <option :value="shop.id" x-text="shop.name"></option>
                </template>
            </select>
        </div>
    </div>

    <div>
        <label class="mi-field-label"><i class="fas fa-note-sticky"></i> Notes</label>
        <textarea name="notes" rows="2" class="mi-input block w-full" placeholder="Optional — reason, urgency…">{{ old('notes') }}</textarea>
    </div>

    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div>
                <p class="mi-field-label !mb-0"><i class="fas fa-list"></i> Line Items</p>
                <p class="text-xs text-gray-400 mt-0.5">Source availability shown for reference</p>
            </div>
            <button type="button" @click="addRow()" class="mi-btn-ghost text-sm"><i class="fas fa-plus text-xs"></i> Add Line</button>
        </div>

        <template x-for="(row, index) in rows" :key="index">
            <div class="mi-card p-4 mb-3">
                <div class="mi-form-grid">
                    <div>
                        <label class="mi-field-label text-gray-500">Product</label>
                        <select :name="`items[${index}][product_id]`" class="mi-select" x-model="row.product_id" required>
                            <option value="">Select…</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->part_number }} — {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label text-gray-500">Quantity Requested</label>
                        <input type="number" step="0.01" min="0.01" :name="`items[${index}][requested_quantity]`"
                               class="mi-input block w-full" x-model="row.requested_quantity" required>
                        <p class="text-xs mt-1" x-show="row.product_id && availableFor(row.product_id) !== null">
                            <span class="text-gray-400">Available at source:</span>
                            <strong class="text-gray-700" x-text="Number(availableFor(row.product_id)).toLocaleString(undefined, {maximumFractionDigits: 2})"></strong>
                        </p>
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="removeRow(index)" class="mi-action del"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </template>

        <p x-show="loadingAvailability" class="text-xs text-gray-400"><i class="fas fa-spinner fa-spin"></i> Checking availability…</p>
    </div>
</div>
