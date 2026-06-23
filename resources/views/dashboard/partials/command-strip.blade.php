@props(['strip' => ['cards' => [], 'summary' => []]])

@if (! empty($strip['cards']))
    <div class="db-command-strip">
        <div class="db-rich-kpi-grid">
            @foreach ($strip['cards'] as $card)
                @if ($card)
                    @include('dashboard.partials.kpi-rich-card', ['kpi' => $card])
                @endif
            @endforeach
        </div>
        @include('dashboard.partials.summary-bar', ['items' => $strip['summary'] ?? []])
    </div>
@endif
