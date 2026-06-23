@props([
    'greeting' => '',
    'subtitle' => '',
    'roleLabel' => 'User',
    'highlights' => [],
])

<div class="db-hero-card">
    <div class="db-hero-card-glow" aria-hidden="true"></div>
    <div class="db-hero-card-inner">
        <div class="db-hero-main">
            <div class="db-hero-icon"><i class="fas fa-gauge-high"></i></div>
            <div class="db-hero-copy">
                <div class="db-hero-title-row">
                    <h1 class="db-hero-title">{{ $greeting }}</h1>
                    <span class="db-role-badge">
                        <i class="fas fa-user-shield text-[0.55rem]"></i>
                        {{ $roleLabel }}
                    </span>
                </div>
                <p class="db-hero-subtitle">{{ $subtitle }}</p>
            </div>
        </div>

        <div class="db-hero-aside">
            <p class="db-hero-date">
                <i class="far fa-calendar text-orange-400"></i>
                {{ now()->format('l, j M Y') }}
            </p>
            @if (count($highlights) > 0)
                <div class="db-hero-highlights">
                    @foreach ($highlights as $item)
                        <div class="db-hero-pill">
                            <i class="fas {{ $item['icon'] }}"></i>
                            <span class="db-hero-pill-label">{{ $item['label'] }}</span>
                            <span class="db-hero-pill-value">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
