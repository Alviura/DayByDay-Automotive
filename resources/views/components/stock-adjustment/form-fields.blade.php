@props(['warehouses' => collect(), 'shops' => collect(), 'products' => collect()])

<div
    x-data="{
        locationType: @js(old('location_type', 'warehouse')),
        locationId: @js(old('location_id', $warehouses->first()?->id ?? '')),
        rows: @js(old('items', [['product_id' => '', 'counted_quantity' => '', 'system_quantity' => '']])),
        async fetchSystemQty(index) {
            const row = this.rows[index];
            if (! row.product_id || ! this.locationId) return;
            const res = await fetch(`{{ route('inventory.balance') }}?product_id=${row.product_id}&location_type=${this.locationType}&location_id=${this.locationId}`);
            const data = await res.json();
            row.system_quantity = data.system_quantity;
        },
        addRow() { this.rows.push({ product_id: '', counted_quantity: '', system_quantity: '' }); },
        removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); }
    }"
    class="space-y-6"
>
    <div class="mi-form-grid">
        <div>
            <label class="mi-field-label"><i class="fas fa-map-pin"></i> Location Type</label>
            <select name="location_type" class="mi-select" x-model="locationType" required>
                <option value="warehouse">Warehouse</option>
                <option value="shop">Shop</option>
            </select>
            <x-input-error :messages="$errors->get('location_type')" class="mt-1.5" />
        </div>
        <div>
            <label class="mi-field-label"><i class="fas fa-warehouse"></i> Location</label>
            <select x-show="locationType === 'warehouse'" x-cloak
                    :name="locationType === 'warehouse' ? 'location_id' : undefined'"
                    class="mi-select" x-model="locationId" required>
                <option value="">Select warehouse…</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                @endforeach
            </select>
            <select x-show="locationType === 'shop'" x-cloak
                    :name="locationType === 'shop' ? 'location_id' : undefined'"
                    class="mi-select" x-model="locationId" required>
                <option value="">Select shop…</option>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }} ({{ $shop->code }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('location_id')" class="mt-1.5" />
        </div>
        <div>
            <label for="reason" class="mi-field-label"><i class="fas fa-tag"></i> Reason</label>
            <select id="reason" name="reason" class="mi-select" required>
                @foreach (['count_variance' => 'Count variance', 'damaged' => 'Damaged', 'lost' => 'Lost / missing', 'correction' => 'Correction', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('reason')" class="mt-1.5" />
        </div>
    </div>

    <div>
        <label for="notes" class="mi-field-label"><i class="fas fa-align-left"></i> Notes</label>
        <textarea id="notes" name="notes" rows="2" class="mi-input block w-full resize-y"
                  placeholder="Optional context for the approver…">{{ old('notes') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
    </div>

    <div>
        <div class="flex items-center justify-between gap-3 mb-3">
            <p class="mi-field-label !mb-0"><i class="fas fa-list"></i> Count Lines</p>
            <button type="button" @click="addRow()" class="mi-btn-ghost text-sm">
                <i class="fas fa-plus text-xs"></i> Add Line
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="mi-card p-4">
                    <div class="mi-form-grid">
                        <div>
                            <label class="mi-field-label text-gray-500">Product</label>
                            <select class="mi-select" :name="`items[${index}][product_id]`" x-model="row.product_id"
                                    @change="fetchSystemQty(index)" required>
                                <option value="">Select product…</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->part_number }} — {{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mi-field-label text-gray-500">System Qty</label>
                            <input type="text" class="mi-input block w-full bg-gray-50" readonly
                                   :value="row.system_quantity !== '' ? row.system_quantity : '—'">
                        </div>
                        <div>
                            <label class="mi-field-label text-gray-500">Counted Qty</label>
                            <input type="number" step="0.01" min="0" class="mi-input block w-full"
                                   :name="`items[${index}][counted_quantity]`" x-model="row.counted_quantity" required>
                        </div>
                        <div class="flex items-end">
                            <button type="button" @click="removeRow(index)" class="mi-action del" title="Remove line">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <x-input-error :messages="$errors->get('items')" class="mt-1.5" />
        <x-input-error :messages="$errors->get('items.*')" class="mt-1.5" />
    </div>
</div>
