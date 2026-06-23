@props(['sales' => collect()])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">At Cash Desk</h2>
        @can('sales.create')
            <a href="{{ route('sales.desk') }}" class="text-xs font-semibold text-orange-600 hover:underline">Open desk</a>
        @endcan
    </div>
    @if ($sales->isEmpty())
        <div class="db-empty"><i class="fas fa-hourglass-half mb-2 block text-lg opacity-40"></i>No held orders — desk is clear.</div>
    @else
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Waiting</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}">{{ $sale->receipt_number }}</a>
                            </td>
                            <td>{{ $sale->items_count ?? '—' }}</td>
                            <td class="font-semibold">{{ number_format($sale->total, 0) }}</td>
                            <td class="text-gray-400">{{ ($sale->submitted_at ?? $sale->created_at)?->diffForHumans(short: true) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
