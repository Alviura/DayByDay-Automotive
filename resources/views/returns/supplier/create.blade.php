<x-app-layout title="New Supplier Return">

    @push('styles')
        <x-module.page-index-styles />
        @include('returns.partials.page-styles')
    @endpush

    @php
        $initialRows = collect(old('items', $prefillProductId
            ? [['product_id' => (string) $prefillProductId, 'quantity' => '', 'condition' => 'damaged']]
            : [['product_id' => '', 'quantity' => '', 'condition' => 'damaged']]
        ))->map(fn ($row) => array_merge($row, ['available' => null]))->values()->all();
        $defaultWarehouseId = old('warehouse_id', $prefillWarehouseId) ?? $warehouses->first()?->id;
    @endphp

    <div class="mi-page space-y-5" x-data="supplierReturnForm({
        availabilityUrl: @js(route('supplier-returns.availability')),
        warehouseId: @js((string) $defaultWarehouseId),
        initialRows: @js($initialRows),
    })">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-truck-ramp-box"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">New Supplier Return</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Return stock from a warehouse back to the supplier.</p>
                </div>
            </div>
            <a href="{{ route('supplier-returns.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </div>

        <div class="mi-form-split">
            <form method="POST" action="{{ route('supplier-returns.store') }}" class="mi-form-main space-y-5">
                @csrf

                <div class="mi-card p-5 space-y-4">
                    <div class="mi-form-grid">
                        <div>
                            <label class="mi-field-label">Supplier</label>
                            <select name="supplier_id" class="mi-select" required>
                                <option value="">Select supplier…</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mi-field-label">From warehouse</label>
                            <select name="warehouse_id" class="mi-select" required x-model="warehouseId" @change="refreshAllAvailability()">
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $prefillWarehouseId) == $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="mi-span-full">
                            <label class="mi-field-label">Reason</label>
                            <input type="text" name="reason" class="mi-input block w-full" value="{{ old('reason') }}" required
                                   placeholder="e.g. Defective batch, wrong goods received">
                            @error('reason')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="mi-card p-5 space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm font-semibold text-gray-800">Line items</p>
                        <button type="button" @click="addRow()" class="mi-btn-ghost text-sm">
                            <i class="fas fa-plus text-xs"></i> Add line
                        </button>
                    </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="mi-card p-4 border border-gray-100">
                            <div class="mi-form-grid">
                                <div>
                                    <label class="mi-field-label text-gray-500">Product</label>
                                    <select :name="`items[${index}][product_id]`" class="mi-select" x-model="row.product_id"
                                            @change="fetchAvailability(index)" required>
                                        <option value="">Select product…</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->part_number }} — {{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mi-field-label text-gray-500">Return qty</label>
                                    <input type="number" step="0.01" min="0.01" class="mi-input block w-full"
                                           :name="`items[${index}][quantity]`" x-model="row.quantity" required>
                                    <p class="mt-1" x-show="row.product_id && row.available !== null">
                                        <span :class="row.quantity <= row.available ? 'rt-avail-ok' : 'rt-avail-low'">
                                            <i class="fas fa-warehouse text-[0.6rem]"></i>
                                            <span x-text="row.available + ' available'"></span>
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="mi-field-label text-gray-500">Condition</label>
                                    <select :name="`items[${index}][condition]`" class="mi-select" x-model="row.condition">
                                        <option value="good">Good</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="button" @click="removeRow(index)" class="mi-action del" x-show="rows.length > 1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('supplier-returns.index') }}" class="mi-btn-ghost">Cancel</a>
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-save text-xs"></i> Save as Draft
                    </button>
                </div>
            </form>

            <x-return.form-guide type="supplier" />
        </div>
    </div>

    @push('scripts')
    <script>
        function supplierReturnForm(config) {
            const defaultRow = (productId = '') => ({
                product_id: productId ? String(productId) : '',
                quantity: '',
                condition: 'damaged',
                available: null,
            });

            return {
                availabilityUrl: config.availabilityUrl,
                warehouseId: config.warehouseId,
                rows: config.initialRows.length ? config.initialRows : [defaultRow()],
                init() {
                    this.rows.forEach((_, i) => this.fetchAvailability(i));
                },
                addRow() {
                    this.rows.push(defaultRow());
                },
                removeRow(i) {
                    if (this.rows.length > 1) this.rows.splice(i, 1);
                },
                async refreshAllAvailability() {
                    for (let i = 0; i < this.rows.length; i++) {
                        await this.fetchAvailability(i);
                    }
                },
                async fetchAvailability(index) {
                    const row = this.rows[index];
                    if (!this.warehouseId || !row.product_id) {
                        row.available = null;
                        return;
                    }
                    const url = new URL(this.availabilityUrl, window.location.origin);
                    url.searchParams.set('warehouse_id', this.warehouseId);
                    url.searchParams.set('product_id', row.product_id);
                    const res = await fetch(url);
                    const data = await res.json();
                    row.available = data.available ?? 0;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
