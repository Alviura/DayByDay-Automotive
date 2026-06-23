@props(['items' => collect(), 'location' => null])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">Low Stock Alert</h2>
        @can('inventory.view')
            <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">Inventory</a>
        @endcan
    </div>
    @if ($items->isEmpty())
        <div class="db-empty"><i class="fas fa-circle-check mb-2 block text-lg text-green-400"></i>All stocked items above reorder level.</div>
    @else
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>On Hand</th>
                        <th>Reorder</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $balance)
                        <tr>
                            <td>
                                <span class="font-mono text-xs text-gray-400">{{ $balance->product?->part_number }}</span>
                                <span class="block font-medium text-gray-800">{{ Str::limit($balance->product?->name, 28) }}</span>
                            </td>
                            <td class="font-semibold text-amber-600">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                            <td class="text-gray-400">{{ number_format($balance->product?->reorder_level ?? 0, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
