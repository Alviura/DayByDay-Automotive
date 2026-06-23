@props(['products' => collect()])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">Low Stock Alerts</h2>
        <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">Inventory</a>
    </div>
    @if ($products->isEmpty())
        <div class="db-empty"><i class="fas fa-circle-check mb-2 block text-lg text-green-400"></i>All products above reorder level.</div>
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
                    @foreach ($products as $product)
                        <tr>
                            <td>
                                <span class="font-mono text-[0.65rem] text-gray-400">{{ $product->part_number }}</span>
                                <span class="block font-medium text-gray-800 truncate">{{ Str::limit($product->name, 26) }}</span>
                            </td>
                            <td class="font-semibold text-amber-600">{{ number_format($product->total_on_hand, 0) }}</td>
                            <td class="text-gray-400">{{ number_format($product->reorder_level, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
