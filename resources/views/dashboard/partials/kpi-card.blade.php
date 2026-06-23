<div class="mi-kpi mi-kpi-{{ $kpi['color'] }}">
    <div>
        <p class="mi-kpi-label">{{ $kpi['label'] }}</p>
        <p class="mi-kpi-value {{ $kpi['color'] === 'orange' ? 'orange' : '' }}">{{ $kpi['value'] }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $kpi['sub'] }}</p>
    </div>
    <div class="mi-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
</div>
