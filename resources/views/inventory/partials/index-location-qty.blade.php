@props([
    'balances',
    'totalQty',
    'type' => 'warehouse',
])

@php
    $isWarehouse = $type === 'warehouse';
    $locationClass = $isWarehouse ? \App\Models\Warehouse::class : \App\Models\Shop::class;
    $activeBalances = $balances
        ->filter(fn ($b) => $b->location_type === $locationClass && (float) $b->quantity_on_hand > 0)
        ->values();
    $total = (float) $totalQty;
@endphp

<div class="inv-qty-cell {{ $isWarehouse ? 'inv-qty-cell-wh' : 'inv-qty-cell-sh' }}">
    @if ($total > 0)
        <div class="inv-qty-cell-head">
            <span class="inv-qty-cell-icon">
                <i class="fas fa-{{ $isWarehouse ? 'warehouse' : 'store' }}"></i>
            </span>
            <span class="inv-qty-cell-value">{{ number_format($total, 0) }}</span>
        </div>
        @if ($activeBalances->count() === 1)
            <p class="inv-qty-cell-loc">{{ $activeBalances->first()->location?->name }}</p>
        @elseif ($activeBalances->count() > 1)
            <ul class="inv-qty-cell-breakdown">
                @foreach ($activeBalances as $balance)
                    <li>
                        <span>{{ $balance->location?->name }}</span>
                        <strong>{{ number_format($balance->quantity_on_hand, 0) }}</strong>
                    </li>
                @endforeach
            </ul>
        @endif
    @else
        <span class="inv-qty-cell-empty">—</span>
    @endif
</div>
