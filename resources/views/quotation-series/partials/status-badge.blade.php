@php
    $badgeClass = match ($series->status) {
        'quotation_draft' => 'qs-badge qs-badge-violet',
        'order_draft', 'draft', 'cost_analysis' => 'qs-badge qs-badge-amber',
        'pending_approval' => 'qs-badge qs-badge-orange',
        'approved' => 'qs-badge qs-badge-blue',
        'po_generated' => 'qs-badge qs-badge-indigo',
        'in_transit' => 'qs-badge qs-badge-cyan',
        'received' => 'qs-badge qs-badge-green',
        'closed' => 'qs-badge qs-badge-slate',
        'cancelled' => 'qs-badge qs-badge-red',
        default => 'qs-badge qs-badge-slate',
    };
@endphp
<span class="{{ $badgeClass }}">{{ $series->statusLabel() }}</span>
