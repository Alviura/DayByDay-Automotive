<x-app-layout :title="'Receive '.$stockTransfer->transfer_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-truck-ramp-box"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Receive Transfer</h1>
                    <p class="mt-0.5 text-sm text-gray-500">
                        <span class="font-mono">{{ $stockTransfer->transfer_number }}</span>
                        · {{ $stockTransfer->routeLabel() }}
                    </p>
                </div>
            </div>
            <a href="{{ route('stock-transfers.show', $stockTransfer) }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Transfer
            </a>
        </div>

        <div class="tr-show-banner">
            <i class="fas fa-info-circle text-orange-500"></i>
            <p class="text-sm text-gray-600">Confirm quantities received at <strong>{{ $stockTransfer->destinationLabel() }}</strong>.</p>
        </div>

        <form method="POST" action="{{ route('stock-transfers.receive.store', $stockTransfer) }}" class="mi-card">
            @csrf
            <div class="mi-card-head">
                <p class="text-sm font-semibold text-gray-800">Receipt lines</p>
            </div>
            <div class="p-4 border-b border-gray-100">
                <label class="mi-field-label"><i class="fas fa-note-sticky"></i> Receipt notes</label>
                <textarea name="notes" rows="2" class="mi-input block w-full" placeholder="Optional">{{ old('notes') }}</textarea>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Remaining</th>
                            <th>Received Qty</th>
                            <th>Damaged Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stockTransfer->items as $index => $item)
                            @if ($item->remainingQuantity() > 0)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-sm">{{ $item->product->part_number }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                    </td>
                                    <td class="font-medium">{{ number_format($item->remainingQuantity(), 2) }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][received_quantity]"
                                               class="mi-input w-28" value="{{ old('items.'.$index.'.received_quantity', $item->remainingQuantity()) }}" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][damaged_quantity]"
                                               class="mi-input w-28" value="{{ old('items.'.$index.'.damaged_quantity', 0) }}">
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mi-card-foot flex justify-end">
                <button type="submit" class="mi-btn-orange">
                    <i class="fas fa-check text-xs"></i> Post Receipt to Inventory
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
