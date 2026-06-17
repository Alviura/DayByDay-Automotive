@props([
    'subtitle' => 'Tips',
])

<aside class="mi-guide">
    <div class="mi-guide-head">
        <div class="mi-guide-icon">
            <i class="fas fa-circle-info"></i>
        </div>
        <div>
            <h2 class="mi-guide-title">Quick Guide</h2>
            <p class="mi-guide-subtitle">{{ $subtitle }}</p>
        </div>
    </div>

    <div class="mi-guide-body">
        {{ $slot }}
    </div>
</aside>
