@php
    $badgeClass = match ($order->status) {
        'draft' => 'po-badge po-badge-slate',
        'sent' => 'po-badge po-badge-blue',
        'partially_received' => 'po-badge po-badge-amber',
        'received' => 'po-badge po-badge-green',
        'closed_short' => 'po-badge po-badge-indigo',
        'cancelled' => 'po-badge po-badge-red',
        default => 'po-badge po-badge-slate',
    };
@endphp
<span class="{{ $badgeClass }}">{{ $order->statusLabel() }}</span>
