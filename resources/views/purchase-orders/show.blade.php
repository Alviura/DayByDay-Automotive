<x-app-layout :title="$purchaseOrder->po_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold">{{ $purchaseOrder->po_number }}</h1>
                    <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier?->name }} · {{ $purchaseOrder->statusLabel() }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('purchase-orders.index') }}" class="mi-btn-ghost">Back</a>
                @if ($purchaseOrder->canReceive())
                    @can('procurement.manage')
                        <a href="{{ route('goods-receipts.create', $purchaseOrder) }}" class="mi-btn-orange">
                            <i class="fas fa-truck-ramp-box text-xs"></i> Receive Goods
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Product</th><th>Ordered</th><th>Received</th><th>Remaining</th><th>Unit Cost</th><th>Line Total</th></tr></thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td>{{ $item->product->part_number }} — {{ $item->product->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ number_format($item->received_quantity, 2) }}</td>
                                <td>{{ number_format($item->remainingQuantity(), 2) }}</td>
                                <td>{{ number_format($item->unit_cost, 2) }}</td>
                                <td>{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($purchaseOrder->goodsReceiptNotes->isNotEmpty())
            <div class="mi-card p-5">
                <p class="text-sm font-semibold mb-2">Goods Receipts</p>
                @foreach ($purchaseOrder->goodsReceiptNotes as $grn)
                    <a href="{{ route('goods-receipts.show', $grn) }}" class="mi-cat-badge mr-2">{{ $grn->grn_number }}</a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
