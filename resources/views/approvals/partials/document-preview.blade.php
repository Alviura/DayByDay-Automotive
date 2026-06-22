@php
    $document = $approval->approvable;
@endphp

@if ($document)
    @switch(true)
        @case($document instanceof \App\Models\TransferRequest)
            @include('approvals.partials.previews.transfer', ['transfer' => $document])
            @break
        @case($document instanceof \App\Models\StockAdjustment)
            @include('approvals.partials.previews.adjustment', ['adjustment' => $document])
            @break
        @case($document instanceof \App\Models\ReturnRecord)
            @include('approvals.partials.previews.return', ['returnRecord' => $document])
            @break
        @case($document instanceof \App\Models\QuotationSeries)
            @include('approvals.partials.previews.quotation-series', ['series' => $document])
            @break
    @endswitch
@endif
