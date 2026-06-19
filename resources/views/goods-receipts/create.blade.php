<x-app-layout :title="'Receive '.$purchaseOrder->po_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-[1.35rem] font-bold">Goods Receipt</h1>
                <p class="text-sm text-gray-500">PO {{ $purchaseOrder->po_number }} — {{ $purchaseOrder->supplier?->name }}</p>
            </div>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="mi-btn-ghost">Back to PO</a>
        </div>

        <form method="POST" action="{{ route('goods-receipts.store', $purchaseOrder) }}" class="mi-card p-6 space-y-5">
            @csrf
            <div class="mi-form-grid">
                <div>
                    <label class="mi-field-label">Receive into Warehouse</label>
                    <select name="warehouse_id" class="mi-select" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mi-span-full">
                    <label class="mi-field-label">Notes</label>
                    <textarea name="notes" rows="2" class="mi-input block w-full">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Remaining</th>
                            <th>Received Qty</th>
                            <th>Damaged Qty</th>
                            <th>Unit Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $index => $item)
                            @if ($item->remainingQuantity() > 0)
                                <tr>
                                    <td>
                                        {{ $item->product->part_number }}
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                    </td>
                                    <td>{{ number_format($item->remainingQuantity(), 2) }}</td>
                                    <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][received_quantity]" class="mi-input" value="{{ $item->remainingQuantity() }}" required></td>
                                    <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][damaged_quantity]" class="mi-input" value="0"></td>
                                    <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_cost]" class="mi-input" value="{{ $item->unit_cost }}"></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="mi-btn-orange"><i class="fas fa-check text-xs"></i> Post Receipt to Inventory</button>
            </div>
        </form>
    </div>
</x-app-layout>
