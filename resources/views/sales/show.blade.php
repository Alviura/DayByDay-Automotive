<x-app-layout :title="$sale->receipt_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[1.35rem] font-bold">{{ $sale->receipt_number }}</h1>
                    <span class="mi-status-pending">{{ $sale->statusLabel() }}</span>
                </div>
                <p class="text-sm text-gray-500">{{ $sale->shop?->name }} · {{ $sale->cashier?->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sales.index') }}" class="mi-btn-ghost">Back</a>
                @if ($sale->status === 'completed')
                    <a href="{{ route('receipts.show', $sale) }}" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Receipt</a>
                @endif
                @if ($sale->canComplete())
                    <a href="{{ route('sales.pos', ['shop_id' => $sale->shop_id, 'sale' => $sale->id]) }}" class="mi-btn-orange">Resume in POS</a>
                @endif
                @if ($sale->canReverse())
                    @can('sales.reverse')
                        <form action="{{ route('sales.reverse', $sale) }}" method="POST" class="inline" onsubmit="return confirm('Reverse this sale and restore stock?');">
                            @csrf
                            <button type="submit" class="mi-btn-ghost text-red-600">Reverse</button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Subtotal</p><p class="mi-kpi-value">{{ number_format($sale->subtotal, 2) }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Discount</p><p class="mi-kpi-value">{{ number_format($sale->discount_total, 2) }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value orange">{{ number_format($sale->total, 2) }}</p></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Paid</p><p class="mi-kpi-value">{{ number_format($sale->amount_paid, 2) }}</p></div></div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Discount</th><th>Line Total</th></tr></thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr>
                                <td>{{ $item->product->part_number }} — {{ $item->product->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->discount, 2) }}</td>
                                <td>{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($sale->payments->isNotEmpty())
            <div class="mi-card p-5">
                <p class="text-sm font-semibold mb-3">Payments</p>
                @foreach ($sale->payments as $payment)
                    <div class="flex justify-between text-sm py-1">
                        <span>{{ $payment->methodLabel() }} @if($payment->reference)<span class="text-gray-400">({{ $payment->reference }})</span>@endif</span>
                        <span class="font-medium">{{ number_format($payment->amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
