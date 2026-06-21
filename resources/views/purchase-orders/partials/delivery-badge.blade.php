@php
    $badgeClass = match ($order->delivery_status) {
        'pending' => 'po-badge po-badge-slate',
        'in_transit' => 'po-badge po-badge-cyan',
        'delivered' => 'po-badge po-badge-green',
        default => 'po-badge po-badge-slate',
    };
@endphp
<span class="{{ $badgeClass }}">{{ $order->deliveryLabel() }}</span>
