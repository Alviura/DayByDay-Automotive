<div class="mi-card">
    <div class="mi-card-head">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fas fa-rotate-left text-gray-400 text-sm"></i>
            <span class="text-sm font-semibold">Return Lines</span>
        </div>
    </div>
    <div class="ap-preview-meta">
        <span class="ap-preview-meta-item">
            <i class="fas fa-{{ $returnRecord->type === 'supplier' ? 'truck' : 'user' }}"></i>
            <strong>{{ ucfirst($returnRecord->type) }} return</strong>
        </span>
        @if ($returnRecord->type === 'customer' && $returnRecord->sale)
            <span class="ap-preview-meta-item"><i class="fas fa-receipt"></i> Sale {{ $returnRecord->sale->receipt_number }}</span>
        @elseif ($returnRecord->supplier)
            <span class="ap-preview-meta-item"><i class="fas fa-building"></i> {{ $returnRecord->supplier->name }}</span>
        @endif
        @if ((float) $returnRecord->refund_amount > 0)
            <span class="ap-preview-meta-item"><i class="fas fa-coins"></i> Refund KES {{ number_format((float) $returnRecord->refund_amount, 2) }}</span>
        @endif
    </div>
    <div class="mi-table-wrap">
        <table class="mi-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($returnRecord->items as $item)
                    <tr>
                        <td>
                            <p class="mi-pkg-name">{{ $item->product->part_number }}</p>
                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                        </td>
                        <td class="font-medium">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ number_format((float) $item->quantity * (float) $item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
