@props(['kpis' => [], 'title' => null, 'icon' => null, 'class' => ''])

@if (count($kpis) > 0)
    <div class="db-section {{ $class }}">
        @if ($title)
            <div class="db-section-head">
                <p class="db-section-title">
                    @if ($icon)<i class="fas {{ $icon }}"></i>@endif
                    {{ $title }}
                </p>
            </div>
        @endif
        <div class="db-kpi-grid {{ str_contains($class, 'finance') ? 'db-kpi-grid--finance' : '' }}">
            @foreach ($kpis as $kpi)
                @if ($kpi)
                    @if (! empty($kpi['url']))
                        <a href="{{ $kpi['url'] }}" class="db-kpi-link">
                            @include('dashboard.partials.kpi-card', ['kpi' => $kpi])
                        </a>
                    @else
                        @include('dashboard.partials.kpi-card', ['kpi' => $kpi])
                    @endif
                @endif
            @endforeach
        </div>
    </div>
@endif
