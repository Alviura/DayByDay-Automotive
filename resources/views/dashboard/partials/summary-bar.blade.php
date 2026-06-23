@props(['items' => []])

@if (count($items) > 0)
    <div class="db-summary-bar">
        @foreach ($items as $item)
            @if (! empty($item['url']))
                <a href="{{ $item['url'] }}" class="db-summary-item">
            @else
                <div class="db-summary-item">
            @endif
                <p class="db-summary-label">{{ $item['label'] }}</p>
                <p class="db-summary-value db-summary-value--{{ $item['tone'] ?? 'default' }}">
                    @if (! empty($item['status']))
                        <span class="db-summary-dot db-summary-dot--{{ $item['status'] }}"></span>
                    @endif
                    {{ $item['value'] }}
                </p>
                @if (! empty($item['sub']))
                    <p class="db-summary-sub">{{ $item['sub'] }}</p>
                @endif
            @if (! empty($item['url']))
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
@endif
