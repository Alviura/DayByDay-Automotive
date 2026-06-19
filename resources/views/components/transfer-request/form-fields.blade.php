@props(['warehouses' => collect(), 'shops' => collect(), 'products' => collect()])

<div
    x-data="{
        type: @js(old('type', 'warehouse_to_shop')),
        sourceId: @js(old('source_id', $warehouses->first()?->id ?? '')),
        destinationId: @js(old('destination_id', $shops->first()?->id ?? '')),
        rows: @js(old('items', [['product_id' => '', 'requested_quantity' => '']])),
        addRow() { this.rows.push({ product_id: '', requested_quantity: '' }); },
        removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); }
    }"
    class="space-y-6"
>
    <div class="mi-form-grid">
        <div>
            <label class="mi-field-label">Transfer Type</label>
            <select name="type" class="mi-select" x-model="type" required>
                <option value="warehouse_to_shop">Warehouse → Shop</option>
                <option value="inter_shop">Shop → Shop</option>
            </select>
        </div>
        <div>
            <label class="mi-field-label">Source</label>
            <select x-show="type === 'warehouse_to_shop'" x-cloak
                    :name="type === 'warehouse_to_shop' ? 'source_id' : undefined'"
                    class="mi-select" x-model="sourceId" required>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <select x-show="type === 'inter_shop'" x-cloak
                    :name="type === 'inter_shop' ? 'source_id' : undefined'"
                    class="mi-select" x-model="sourceId" required>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mi-field-label">Destination Shop</label>
            <select name="destination_id" class="mi-select" x-model="destinationId" required>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mi-field-label">Notes</label>
        <textarea name="notes" rows="2" class="mi-input block w-full">{{ old('notes') }}</textarea>
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="mi-field-label !mb-0">Line Items</p>
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
                        <label class="mi-field-label text-gray-500">Quantity</label>
                        <input type="number" step="0.01" min="0.01" :name="`items[${index}][requested_quantity]`"
                               class="mi-input block w-full" x-model="row.requested_quantity" required>
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="removeRow(index)" class="mi-action del"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
