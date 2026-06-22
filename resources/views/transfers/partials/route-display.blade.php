@php
    $isWhSource = $transferRequest->isWarehouseSource();
    $isWhDest = $transferRequest->isWarehouseDestination();
@endphp

<div class="tr-route">
    <span class="tr-route-node {{ $isWhSource ? 'tr-route-node-wh' : 'tr-route-node-sh' }}">
        <i class="fas fa-{{ $isWhSource ? 'warehouse' : 'store' }} text-[0.55rem]"></i>
        <span>{{ $transferRequest->source?->name ?? '—' }}</span>
    </span>
    <i class="fas fa-arrow-right tr-route-arrow"></i>
    <span class="tr-route-node {{ $isWhDest ? 'tr-route-node-wh' : 'tr-route-node-sh' }}">
        <i class="fas fa-{{ $isWhDest ? 'warehouse' : 'store' }} text-[0.55rem]"></i>
        <span>{{ $transferRequest->destination?->name ?? '—' }}</span>
    </span>
</div>
