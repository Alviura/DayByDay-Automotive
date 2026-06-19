<x-app-layout title="New Customer Return">
    @push('styles')<x-module.page-index-styles />@endpush

    <div class="mi-page space-y-5" x-data="customerReturnForm('{{ route('customer-returns.sale-items', ['sale' => '__ID__']) }}')">
        <div class="flex items-start gap-3 mb-2">
            <div class="mi-page-icon"><i class="fas fa-rotate-left"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold">New Customer Return</h1>
                <p class="text-sm text-gray-500">Select a completed sale and specify items to return.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('customer-returns.store') }}" class="mi-card p-6 space-y-5">
            @csrf
            <div class="mi-form-grid">
                <div>
                    <label class="mi-field-label">Sale / Receipt</label>
                    <select name="sale_id" class="mi-select" required x-model="saleId" @change="loadSaleItems()">
                        <option value="">Select sale…</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}">{{ $sale->receipt_number }} — {{ $sale->shop?->name }} ({{ $sale->sold_at?->format('d M Y') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mi-span-full">
                    <label class="mi-field-label">Reason</label>
                    <input type="text" name="reason" class="mi-input block w-full" value="{{ old('reason') }}" required placeholder="e.g. Wrong part, defective">
                </div>
            </div>

            <div x-show="lines.length" x-cloak>
                <p class="mi-field-label mb-3">Return Lines</p>
                <template x-for="(line, index) in lines" :key="line.product_id">
                    <div class="mi-card p-4 mb-3">
                        <input type="hidden" :name="`items[${index}][product_id]`" :value="line.product_id">
                        <p class="font-medium text-sm" x-text="line.part_number + ' — ' + line.name"></p>
                        <p class="text-xs text-gray-500 mb-3">Sold: <span x-text="line.sold_quantity"></span></p>
                        <div class="mi-form-grid">
                            <div>
                                <label class="mi-field-label text-gray-500">Qty</label>
                                <input type="number" step="0.01" min="0.01" :max="line.sold_quantity" class="mi-input block w-full"
                                       :name="`items[${index}][quantity]`" x-model.number="line.quantity" required>
                            </div>
                            <div>
                                <label class="mi-field-label text-gray-500">Unit Price</label>
                                <input type="number" step="0.01" class="mi-input block w-full"
                                       :name="`items[${index}][unit_price]`" x-model.number="line.unit_price">
                            </div>
                            <div>
                                <label class="mi-field-label text-gray-500">Condition</label>
                                <select :name="`items[${index}][condition]`" class="mi-select" x-model="line.condition">
                                    <option value="good">Good</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-4">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" :name="`items[${index}][restock]`" value="1" x-model="line.restock" :disabled="line.condition === 'damaged'">
                                    Restock
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('customer-returns.index') }}" class="mi-btn-ghost">Cancel</a>
                <button type="submit" class="mi-btn-orange" :disabled="!lines.length">Create Return</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function customerReturnForm(urlTemplate) {
            return {
                saleId: '',
                lines: [],
                async loadSaleItems() {
                    if (!this.saleId) { this.lines = []; return; }
                    const url = urlTemplate.replace('__ID__', this.saleId);
                    const res = await fetch(url);
                    const data = await res.json();
                    this.lines = data.map(l => ({
                        ...l,
                        quantity: 1,
                        condition: 'good',
                        restock: true,
                    }));
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
