<div class="mi-card">
    <div class="mi-card-head">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fas fa-boxes-stacked text-gray-400 text-sm"></i>
            <span class="text-sm font-semibold">Adjustment Lines</span>
        </div>
    </div>
    <div class="ap-preview-meta">
        <span class="ap-preview-meta-item"><i class="fas fa-location-dot"></i> <strong>{{ $adjustment->locationLabel() }}</strong></span>
        <span class="ap-preview-meta-item"><i class="fas fa-sliders"></i> {{ $adjustment->reasonLabel() }}</span>
    </div>
    <div class="mi-table-wrap">
        <table class="mi-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>System</th>
                    <th>Counted</th>
                    <th>Difference</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adjustment->items as $item)
                    <tr>
                        <td>
                            <p class="mi-pkg-name">{{ $item->product->part_number }}</p>
                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                        </td>
                        <td>{{ number_format((float) $item->system_quantity, 2) }}</td>
                        <td>{{ number_format((float) $item->counted_quantity, 2) }}</td>
                        <td class="font-semibold {{ $item->difference < 0 ? 'text-red-600' : ($item->difference > 0 ? 'text-green-600' : 'text-gray-500') }}">
                            {{ $item->difference > 0 ? '+' : '' }}{{ number_format((float) $item->difference, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
