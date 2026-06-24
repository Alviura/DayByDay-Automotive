<div class="mi-card">
    <div class="mi-card-head">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fas fa-file-invoice-dollar text-gray-400 text-sm"></i>
            <span class="text-sm font-semibold">Quotation Lines</span>
        </div>
    </div>
    @if (config('approvals.modules.quotation-series.legacy'))
        <div class="px-6 pt-4">
            <div class="ap-legacy-note">
                <i class="fas fa-circle-info"></i>
                <p>New quotation orders are auto-approved on confirm. This inbox entry is from the legacy approval workflow or historical data.</p>
            </div>
        </div>
    @endif
    <div class="ap-preview-meta">
        @if ($series->supplier)
            <span class="ap-preview-meta-item"><i class="fas fa-truck"></i> <strong>{{ $series->supplier->name }}</strong></span>
        @endif
        <span class="ap-preview-meta-item"><i class="fas fa-coins"></i> Actual cost KES {{ number_format((float) $series->total_actual_cost, 2) }}</span>
        <span class="ap-preview-meta-item"><i class="fas fa-list"></i> {{ $series->items->count() }} lines</span>
    </div>
    <div class="mi-table-wrap">
        <table class="mi-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Cost</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($series->items->take(12) as $item)
                    <tr>
                        <td>
                            <p class="mi-pkg-name">{{ $item->product->part_number }}</p>
                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                        </td>
                        <td>
                            {{ number_format($item->displayOrderQuantity(), 0) }}
                            @if ($item->isBundledSupplierLine())
                                <span class="block text-[0.62rem] text-gray-400">{{ $item->product->supplierQuantityLabel() }}</span>
                            @endif
                            <span class="block text-[0.62rem] text-gray-400">{{ number_format($item->displayStockQuantity(), 0) }} pcs</span>
                        </td>
                        <td>{{ number_format($item->landedUnitCost(), 2) }}</td>
                        <td>{{ number_format((float) ($item->actual_total_cost ?? $item->total_cost ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($series->items->count() > 12)
        <p class="px-6 py-3 text-xs text-gray-400 border-t border-gray-100">Showing 12 of {{ $series->items->count() }} lines — open the series for the full list.</p>
    @endif
</div>
