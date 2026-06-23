@php
    $color = $kpi['color'] ?? 'blue';
    $tag = ! empty($kpi['url']) ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if (! empty($kpi['url'])) href="{{ $kpi['url'] }}" @endif
    class="db-rich-kpi db-rich-kpi--{{ $color }}"
>
    <div class="db-rich-kpi-top">
        <p class="db-rich-kpi-label">{{ $kpi['label'] }}</p>
        <div class="db-rich-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
    </div>
    <p class="db-rich-kpi-value">
        @if (! empty($kpi['prefix']))<span class="db-rich-kpi-prefix">{{ $kpi['prefix'] }}</span>@endif
        {{ $kpi['value'] }}
    </p>
    @if (! empty($kpi['badges']))
        <div class="db-rich-kpi-badges">
            @foreach ($kpi['badges'] as $badge)
                <span class="db-rich-badge db-rich-badge--{{ $badge['tone'] ?? 'gray' }}">{{ $badge['text'] }}</span>
            @endforeach
        </div>
    @endif
    @if (! empty($kpi['trend']))
        <p class="db-rich-kpi-foot db-rich-kpi-foot--{{ $kpi['trend']['direction'] ?? 'neutral' }}">
            @if (($kpi['trend']['direction'] ?? '') === 'up')
                <i class="fas fa-arrow-trend-up"></i>
            @elseif (($kpi['trend']['direction'] ?? '') === 'down')
                <i class="fas fa-arrow-trend-down"></i>
            @else
                <i class="fas fa-minus"></i>
            @endif
            {{ $kpi['trend']['text'] }}
        </p>
    @elseif (! empty($kpi['alert']))
        <p class="db-rich-kpi-foot db-rich-kpi-foot--alert">
            <i class="fas fa-triangle-exclamation"></i>
            {{ $kpi['alert']['text'] }}
        </p>
    @elseif (! empty($kpi['sub']))
        <p class="db-rich-kpi-foot db-rich-kpi-foot--neutral">{{ $kpi['sub'] }}</p>
    @endif
</{{ $tag }}>
