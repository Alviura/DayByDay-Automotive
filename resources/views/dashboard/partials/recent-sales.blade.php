@props(['sales' => collect(), 'title' => 'Recent Sales', 'showShop' => false])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">{{ $title }}</h2>
        @can('sales.view')
            <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">View all</a>
        @endcan
    </div>
    @if ($sales->isEmpty())
        <div class="db-empty"><i class="fas fa-receipt mb-2 block text-lg opacity-40"></i>No sales yet today.</div>
    @else
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        @if ($showShop)<th>Shop</th>@endif
                        <th>Total</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}">{{ $sale->receipt_number }}</a>
                            </td>
                            @if ($showShop)
                                <td>{{ $sale->shop?->name ?? '—' }}</td>
                            @endif
                            <td class="font-semibold">{{ number_format($sale->total, 0) }}</td>
                            <td class="text-gray-400">{{ $sale->sold_at?->diffForHumans(short: true) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
