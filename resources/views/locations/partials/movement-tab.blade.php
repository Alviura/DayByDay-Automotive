@props(['location', 'locationType', 'movements'])

@php
    $inventoryFilters = ['location_type' => $locationType, 'location_id' => $location->id];
@endphp

<div class="mi-card">
    <div class="mi-card-head">
        <div class="flex items-center justify-between w-full gap-3">
            <div>
                <p class="inv-section-title"><i class="fas fa-right-left"></i> Stock Movement</p>
                <p class="inv-section-sub">Latest ledger entries at this location</p>
            </div>
            @can('inventory.view')
                <a href="{{ route('inventory.movements', $inventoryFilters) }}" class="text-xs text-orange-600 font-semibold hover:underline">
                    View all movements
                </a>
            @endcan
        </div>
    </div>
    <div class="mi-table-wrap">
        <table class="mi-table text-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Balance</th>
                    <th>Reference</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td class="text-gray-500 whitespace-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                        <td><span class="{{ $movement->badgeClass() }}">{{ $movement->transactionLabel() }}</span></td>
                        <td>
                            <p class="font-medium text-sm">{{ $movement->product?->part_number }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($movement->product?->name, 28) }}</p>
                        </td>
                        <td class="{{ $movement->isInbound() ? 'inv-qty-in' : 'inv-qty-out' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 0) }}
                        </td>
                        <td class="font-medium">{{ number_format($movement->balance_after, 0) }}</td>
                        <td>@include('inventory.partials.movement-reference', ['movement' => $movement])</td>
                        <td class="text-gray-500 text-xs">{{ $movement->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="!py-10 text-center text-gray-400">No stock movements at this location yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($movements->isNotEmpty())
        <div class="inv-index-hint">
            <i class="fas fa-circle-info"></i>
            <span>Showing the {{ $movements->count() }} most recent entries.</span>
        </div>
    @endif
</div>
