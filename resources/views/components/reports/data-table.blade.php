@props(['title' => 'Data', 'rows'])

@php
    $first = $rows instanceof \Illuminate\Support\Collection ? $rows->first() : ($rows[0] ?? null);
    if ($first === null) return;
    $headers = is_array($first) ? array_keys($first) : array_keys($first->toArray());
@endphp

<div class="mi-card">
    <div class="mi-card-head"><span class="text-sm font-semibold">{{ $title }}</span></div>
    <div class="mi-table-wrap">
        <table class="mi-table text-sm">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php $cells = is_array($row) ? $row : $row->toArray(); @endphp
                    <tr>
                        @foreach ($headers as $key)
                            <td>
                                @php $val = $cells[$key] ?? ''; @endphp
                                @if (is_numeric($val) && abs($val) >= 1000)
                                    {{ number_format($val, is_float($val + 0) && floor($val) != $val ? 2 : 0) }}
                                @else
                                    {{ is_scalar($val) || $val === null ? $val : json_encode($val) }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
