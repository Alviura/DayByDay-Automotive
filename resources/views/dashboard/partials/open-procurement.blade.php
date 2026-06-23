@props(['series' => collect()])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">Open Procurement</h2>
        <a href="{{ route('quotation-series.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">View all</a>
    </div>
    @if ($series->isEmpty())
        <div class="db-empty"><i class="fas fa-folder-open mb-2 block text-lg opacity-40"></i>No open quotation series.</div>
    @else
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Series</th>
                        <th>Supplier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($series as $item)
                        <tr>
                            <td>
                                <a href="{{ route('quotation-series.show', $item) }}">{{ $item->series_number }}</a>
                                <span class="block text-[0.65rem] text-gray-400 truncate">{{ Str::limit($item->title, 24) }}</span>
                            </td>
                            <td title="{{ $item->supplier?->name }}">{{ Str::limit($item->supplier?->name ?? '—', 16) }}</td>
                            <td class="text-xs font-semibold text-gray-500">{{ $item->statusLabel() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
