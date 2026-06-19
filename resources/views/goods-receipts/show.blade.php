<x-app-layout :title="$goodsReceiptNote->grn_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex justify-between gap-4">
            <div>
                <h1 class="text-[1.35rem] font-bold">{{ $goodsReceiptNote->grn_number }}</h1>
                <p class="text-sm text-gray-500">{{ $goodsReceiptNote->warehouse?->name }} · {{ $goodsReceiptNote->received_at?->format('d M Y H:i') }}</p>
            </div>
            <a href="{{ route('purchase-orders.show', $goodsReceiptNote->purchaseOrder) }}" class="mi-btn-ghost">Back to PO</a>
        </div>
        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Product</th><th>Expected</th><th>Received</th><th>Damaged</th><th>Good Qty</th><th>Unit Cost</th></tr></thead>
                    <tbody>
                        @foreach ($goodsReceiptNote->items as $item)
                            <tr>
                                <td>{{ $item->product->part_number }}</td>
                                <td>{{ number_format($item->expected_quantity, 2) }}</td>
                                <td>{{ number_format($item->received_quantity, 2) }}</td>
                                <td>{{ number_format($item->damaged_quantity, 2) }}</td>
                                <td class="font-medium text-green-600">{{ number_format($item->goodQuantity(), 2) }}</td>
                                <td>{{ number_format($item->unit_cost, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
