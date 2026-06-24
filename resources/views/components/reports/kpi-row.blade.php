@props(['summary'])

@php
    $variants = ['mi-kpi-purple', 'mi-kpi-green', 'mi-kpi-amber', 'mi-kpi-orange'];
    $i = 0;
@endphp

<div class="mi-kpi-row">
    @foreach ($summary as $label => $value)
        @php
            $variant = $variants[$i % count($variants)];
            $i++;
            $display = is_numeric($value) && ! str_contains($label, 'rate') && ! str_contains($label, 'pct')
                ? (str_contains($label, 'revenue') || str_contains($label, 'value') || str_contains($label, 'total') || str_contains($label, 'refund') || str_contains($label, 'margin') || str_contains($label, 'cogs') ? number_format($value, 0) : number_format($value))
                : $value;
        @endphp
        <div class="mi-kpi {{ $variant }}">
            <div>
                <p class="mi-kpi-label">{{ ucwords(str_replace('_', ' ', $label)) }}</p>
                <p class="mi-kpi-value">{{ $display }}{{ str_contains($label, 'rate') || str_contains($label, 'pct') ? '%' : '' }}</p>
            </div>
        </div>
    @endforeach
</div>
