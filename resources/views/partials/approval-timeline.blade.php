@props(['approval'])

@php
    $actions = $approval->actions->sortBy('created_at');
@endphp

<div class="mi-approval-timeline">
    @forelse ($actions as $action)
        <div class="mi-approval-timeline-item mi-approval-timeline-{{ $action->action->tone() }}">
            <div class="mi-approval-timeline-marker">
                <i class="fas {{ $action->action->icon() }}"></i>
            </div>
            <div class="mi-approval-timeline-body">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="mi-approval-timeline-title">{{ $action->action->label() }}</p>
                    <time class="text-xs text-gray-400">{{ $action->created_at->format('d M Y, H:i') }}</time>
                </div>
                <p class="mi-approval-timeline-actor">
                    {{ $action->actor?->name ?? 'System' }}
                </p>
                @if ($action->comments)
                    <p class="mi-approval-timeline-comment">{{ $action->comments }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="mi-show-empty">
            <i class="fas fa-clock"></i>
            <p>No actions recorded yet.</p>
        </div>
    @endforelse
</div>
