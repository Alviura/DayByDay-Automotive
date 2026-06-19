<x-app-layout title="New Supplier Return">
    @push('styles')<x-module.page-index-styles />@endpush
    <x-module.form-page
        title="New Supplier Return"
        subtitle="Return stock from warehouse back to supplier."
        icon="fa-truck-ramp-box"
        card-title="Return Details"
        :back-url="route('supplier-returns.index')"
        :action="route('supplier-returns.store')"
        submit-label="Create Return"
    >
        <div
            x-data="{
                rows: @js(old('items', [['product_id' => '', 'quantity' => '', 'condition' => 'damaged']])),
                addRow() { this.rows.push({ product_id: '', quantity: '', condition: 'damaged' }); },
                removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); }
            }"
            class="space-y-6"
        >
            <div class="mi-form-grid">
                <div>
                    <label class="mi-field-label">Supplier</label>
                    <select name="supplier_id" class="mi-select" required>
                        <option value="">Select…</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mi-field-label">From Warehouse</label>
                    <select name="warehouse_id" class="mi-select" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mi-span-full">
                    <label class="mi-field-label">Reason</label>
                    <input type="text" name="reason" class="mi-input block w-full" value="{{ old('reason') }}" required>
                </div>
            </div>

            <div>
                <div class="flex justify-between mb-3">
                    <p class="mi-field-label !mb-0">Line Items</p>
                    <button type="button" @click="addRow()" class="mi-btn-ghost text-sm"><i class="fas fa-plus text-xs"></i> Add</button>
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
                                <label class="mi-field-label text-gray-500">Qty</label>
                                <input type="number" step="0.01" min="0.01" class="mi-input block w-full" :name="`items[${index}][quantity]`" x-model="row.quantity" required>
                            </div>
                            <div>
                                <label class="mi-field-label text-gray-500">Condition</label>
                                <select :name="`items[${index}][condition]`" class="mi-select" x-model="row.condition">
                                    <option value="good">Good</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removeRow(index)" class="mi-action del"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-module.form-page>
</x-app-layout>
