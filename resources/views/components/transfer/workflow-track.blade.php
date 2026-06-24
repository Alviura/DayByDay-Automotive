@props([
    'steps' => [],
    'currentIndex' => 0,
    'action' => null,
])

@php
    $statusKeys = array_column($steps, 'key');
@endphp

<div class="tr-workflow-track">
    @foreach ($steps as $i => $step)
        @php
            $stepIdx = array_search($step['key'], $statusKeys, true);
            $isDone = $stepIdx !== false && $stepIdx < $currentIndex;
            $isCurrent = $stepIdx === $currentIndex;
            $isAction = $action
                && ($action['step'] ?? null) === $step['key']
                && $isCurrent;
        @endphp

        @if ($isAction && ! empty($action['url']))
            <form action="{{ $action['url'] }}" method="POST" class="tr-workflow-step tr-workflow-step-action {{ $isCurrent ? 'current' : '' }}"
                  data-confirm="{{ $action['confirm'] ?? '' }}"
                  data-confirm-variant="warning"
                  data-confirm-label="{{ $action['confirmLabel'] ?? 'Confirm' }}">
                @csrf
                <button type="submit" class="tr-workflow-step-btn">
                    <div class="tr-workflow-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                    <p class="tr-workflow-label">{{ $step['label'] }}</p>
                </button>
            </form>
        @else
            <div class="tr-workflow-step {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                <div class="tr-workflow-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                <p class="tr-workflow-label">{{ $step['label'] }}</p>
            </div>
        @endif
    @endforeach
</div>
