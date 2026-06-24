<div class="mi-card">
    <div class="mi-card-head">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fas fa-right-left text-gray-400 text-sm"></i>
            <span class="text-sm font-semibold">Transfer Lines</span>
        </div>
    </div>
    <div class="ap-preview-meta">
        <span class="ap-preview-meta-item"><i class="fas fa-route"></i> <strong>{{ $transfer->routeLabel() }}</strong></span>
        <span class="ap-preview-meta-item"><i class="fas fa-tag"></i> {{ $transfer->typeLabel() }}</span>
        @if ($transfer->creator)
            <span class="ap-preview-meta-item"><i class="fas fa-user"></i> {{ $transfer->creator->name }}</span>
        @endif
        @if ($transfer->transferRequest)
            <span class="ap-preview-meta-item"><i class="fas fa-inbox"></i> {{ $transfer->transferRequest->request_number }}</span>
        @endif
    </div>
    <div class="mi-table-wrap">
        <table class="mi-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfer->items as $item)
                    <tr>
                        <td>
                            <p class="mi-pkg-name">{{ $item->product->part_number }}</p>
                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                        </td>
                        <td class="font-medium">{{ number_format((float) $item->quantity, 0) }} {{ $item->product->unit?->abbreviation ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
