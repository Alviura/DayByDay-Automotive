@props([
    'warehouses' => collect(),
    'shops' => collect(),
    'products' => collect(),
    'prefill' => [],
])

@php
    $defaultItems = [['product_id' => (string) ($prefill['product_id'] ?? ''), 'requested_quantity' => '']];
    if (old('items')) {
        $defaultItems = old('items');
    }
@endphp

<div
    x-data="{
        type: @js(old('type', $prefill['type'] ?? 'warehouse_to_shop')),
        sourceId: @js((string) old('source_id', $prefill['source_id'] ?? '')),
        destinationId: @js((string) old('destination_id', $prefill['destination_id'] ?? '')),
        rows: @js($defaultItems),
        availability: {},
        loadingAvailability: false,
        availabilityUrl: @js(route('transfers.availability')),
        addRow() { this.rows.push({ product_id: '', requested_quantity: '' }); },
        removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
        async refreshAvailability() {
            const productIds = this.rows.map(r => r.product_id).filter(Boolean);
            if (!this.sourceId || productIds.length === 0) {
                this.availability = {};
                return;
            }
            this.loadingAvailability = true;
            try {
                const params = new URLSearchParams({
                    type: this.type,
                    source_id: this.sourceId,
                });
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
        qtyWarning(productId, qty) {
            const avail = this.availableFor(productId);
            if (avail === null || !qty) return false;
            return parseFloat(qty) > parseFloat(avail) + 0.001;
        },
    }"
    x-init="
        $watch('type', () => refreshAvailability());
        $watch('sourceId', () => refreshAvailability());
        $watch('rows', () => refreshAvailability(), { deep: true });
        refreshAvailability();
    "
    class="space-y-6"
>
    <div class="mi-form-grid">
        <div>
            <label class="mi-field-label"><i class="fas fa-route"></i> Transfer Type</label>
            <select name="type" class="mi-select" x-model="type" required>
                <option value="warehouse_to_shop">Warehouse → Shop</option>
                <option value="inter_shop">Shop → Shop</option>
            </select>
        </div>
        <div>
            <label class="mi-field-label"><i class="fas fa-warehouse"></i> Source</label>
            <select x-show="type === 'warehouse_to_shop'" x-cloak
                    :name="type === 'warehouse_to_shop' ? 'source_id' : undefined"
                    class="mi-select" x-model="sourceId" required>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <select x-show="type === 'inter_shop'" x-cloak
                    :name="type === 'inter_shop' ? 'source_id' : undefined"
                    class="mi-select" x-model="sourceId" required>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mi-field-label"><i class="fas fa-store"></i> Destination Shop</label>
            <select name="destination_id" class="mi-select" x-model="destinationId" required>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mi-field-label"><i class="fas fa-note-sticky"></i> Notes</label>
        <textarea name="notes" rows="2" class="mi-input block w-full" placeholder="Optional — reason for transfer, urgency…">{{ old('notes') }}</textarea>
    </div>

    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div>
                <p class="mi-field-label !mb-0"><i class="fas fa-list"></i> Line Items</p>
                <p class="text-xs text-gray-400 mt-0.5">Available qty shown for the selected source</p>
            </div>
            <button type="button" @click="addRow()" class="mi-btn-ghost text-sm"><i class="fas fa-plus text-xs"></i> Add Line</button>
        </div>

        <template x-for="(row, index) in rows" :key="index">
            <div class="mi-card p-4 mb-3" :class="qtyWarning(row.product_id, row.requested_quantity) ? 'tr-line-warn' : ''">
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
                        <label class="mi-field-label text-gray-500">Quantity</label>
                        <input type="number" step="0.01" min="0.01" :name="`items[${index}][requested_quantity]`"
                               class="mi-input block w-full" x-model="row.requested_quantity" required>
                        <p class="text-xs mt-1" x-show="row.product_id && availableFor(row.product_id) !== null">
                            <span class="text-gray-400">Available at source:</span>
                            <strong class="text-gray-700" x-text="Number(availableFor(row.product_id)).toLocaleString(undefined, {maximumFractionDigits: 2})"></strong>
                        </p>
                        <p class="text-xs text-red-600 mt-1 font-medium" x-show="qtyWarning(row.product_id, row.requested_quantity)">
                            <i class="fas fa-triangle-exclamation"></i> Exceeds available stock
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
