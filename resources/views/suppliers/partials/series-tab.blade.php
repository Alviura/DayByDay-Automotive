@props(['supplier', 'quotationSeries', 'seriesPipeline'])

<div class="space-y-5">
    <div class="mi-card p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Series pipeline</p>
        <div class="sp-pipeline">
            @foreach ($seriesPipeline as $step)
                <div class="sp-pipe-step">
                    <div class="sp-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                    <span class="sp-pipe-count">{{ $step['count'] }}</span>
                    <span class="sp-pipe-label">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mi-card">
        <div class="mi-card-head">
            <div class="flex items-center justify-between w-full gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Quotation series</p>
                    <p class="text-xs text-gray-400 mt-0.5">Procurement folders for this vendor</p>
                </div>
                @can('procurement.manage')
                    <a href="{{ route('quotation-series.create', ['supplier_id' => $supplier->id]) }}" class="mi-btn-orange text-xs !py-1.5">
                        <i class="fas fa-plus text-[0.6rem]"></i> New series
                    </a>
                @endcan
                @can('procurement.view')
                    <a href="{{ route('quotation-series.index', ['supplier_id' => $supplier->id]) }}" class="text-xs text-orange-600 font-semibold hover:underline">View all</a>
                @endcan
            </div>
        </div>
        @if ($quotationSeries->isNotEmpty())
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead>
                        <tr>
                            <th>Series</th>
                            <th>Type</th>
                            <th>Lines</th>
                            <th>Landing cost</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotationSeries as $series)
                            <tr class="sp-index-row" onclick="window.location='{{ route('quotation-series.show', $series) }}'">
                                <td>
                                    <p class="font-mono font-semibold text-sm">{{ $series->series_number }}</p>
                                    <p class="text-xs text-gray-500 truncate max-w-[14rem]">{{ $series->displayName() }}</p>
                                </td>
                                <td>
                                    <span class="sp-type-pill {{ $series->isImport() ? 'sp-type-import' : 'sp-type-local' }}">
                                        {{ $series->purchaseTypeEnum()->label() }}
                                    </span>
                                </td>
                                <td>{{ $series->items_count }}</td>
                                <td class="font-semibold">{{ number_format($series->total_landing_cost, 0) }}</td>
                                <td>@include('quotation-series.partials.status-badge', ['series' => $series])</td>
                                <td class="text-gray-500">{{ $series->updated_at->format('d M Y') }}</td>
                                <td><i class="fas fa-chevron-right text-xs text-gray-300"></i></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mi-show-empty">
                <i class="fas fa-folder-open"></i>
                <p>No quotation series yet for this supplier.</p>
                @can('procurement.manage')
                    <a href="{{ route('quotation-series.create', ['supplier_id' => $supplier->id]) }}" class="mi-btn-orange mt-4 inline-flex text-sm">
                        <i class="fas fa-plus text-xs"></i> Start first series
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>
