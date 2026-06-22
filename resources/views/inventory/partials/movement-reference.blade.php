@if ($movement->referenceUrl())
    <a href="{{ $movement->referenceUrl() }}" class="inv-ref-link">{{ $movement->referenceLabel() }}</a>
@elseif ($movement->referenceLabel())
    <span class="text-sm text-gray-500">{{ $movement->referenceLabel() }}</span>
@else
    <span class="text-gray-400">—</span>
@endif
