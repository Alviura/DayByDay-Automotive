@props([
    'model',
    'editUrl' => null,
    'indexUrl',
    'editLabel' => 'Edit',
    'indexLabel' => 'Back to List',
    'managePermission' => null,
])

<aside class="mi-guide">
    <div class="mi-guide-head">
        <div class="mi-guide-icon">
            <i class="fas fa-clock-rotate-left"></i>
        </div>
        <div>
            <h2 class="mi-guide-title">Record Info</h2>
            <p class="mi-guide-subtitle">Timestamps and quick actions</p>
        </div>
    </div>

    <div class="mi-guide-body">
        <section class="mi-guide-section mi-guide-section-first">
            <ul class="mi-show-meta">
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-calendar-plus"></i> Created</span>
                    <span class="mi-show-meta-value">{{ $model->created_at->format('d M Y') }}</span>
                    <span class="mi-show-meta-sub">{{ $model->created_at->format('H:i') }}</span>
                </li>
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-calendar-check"></i> Last updated</span>
                    <span class="mi-show-meta-value">{{ $model->updated_at->format('d M Y') }}</span>
                    <span class="mi-show-meta-sub">{{ $model->updated_at->format('H:i') }}</span>
                </li>
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-fingerprint"></i> Record ID</span>
                    <span class="mi-show-meta-value mono">#{{ $model->id }}</span>
                </li>
            </ul>
        </section>

        <section class="mi-guide-section">
            <h3 class="mi-guide-section-title">
                <i class="fas fa-bolt"></i> Quick actions
            </h3>
            <div class="mi-show-actions">
                @if ($editUrl && (! $managePermission || auth()->user()->can($managePermission)))
                    <a href="{{ $editUrl }}" class="mi-btn-orange w-full justify-center">
                        <i class="fas fa-pen text-xs"></i>
                        {{ $editLabel }}
                    </a>
                @endif
                <a href="{{ $indexUrl }}" class="mi-btn-ghost w-full justify-center">
                    <i class="fas fa-list text-xs"></i>
                    {{ $indexLabel }}
                </a>
            </div>
        </section>

        @isset($footer)
            {{ $footer }}
        @endisset
    </div>
</aside>
