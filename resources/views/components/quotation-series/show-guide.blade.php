@props(['series', 'workflowSteps' => []])

@php
    $statusOrder = ['quotation_draft', 'order_draft', 'approved', 'po_generated', 'in_transit', 'received', 'closed'];
    $currentIdx = array_search($series->status, $statusOrder, true);
    if ($currentIdx === false) {
        $currentIdx = 0;
    }
    $nextAction = match (true) {
        $series->canBulkAddItems() && $series->items->isEmpty() => ['tab' => 'quotation', 'label' => 'Add quotation products', 'icon' => 'fa-cart-plus'],
        $series->canProceedToOrder() => ['tab' => 'quotation', 'label' => 'Start order processing', 'icon' => 'fa-arrow-right'],
        $series->canEditPrices() => ['tab' => 'order', 'label' => 'Enter supplier prices', 'icon' => 'fa-pen'],
        $series->canCalculate() => ['tab' => 'order', 'label' => 'Run margin calculation', 'icon' => 'fa-calculator'],
        $series->canConfirm() => ['tab' => 'order', 'label' => 'Confirm & approve order', 'icon' => 'fa-check'],
        $series->canGeneratePo() => ['tab' => 'workflow', 'label' => 'Generate purchase order', 'icon' => 'fa-file-invoice'],
        in_array($series->status, ['po_generated', 'approved']) => ['tab' => 'workflow', 'label' => 'Mark goods in transit', 'icon' => 'fa-truck'],
        $series->status === 'in_transit' => ['tab' => 'workflow', 'label' => 'Receive goods & close', 'icon' => 'fa-box-open'],
        default => null,
    };
@endphp

<x-module.form-guide subtitle="Series at a glance">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title"><i class="fas fa-route"></i> Current phase</h3>
        <p class="mi-guide-text">
            @include('quotation-series.partials.status-badge', ['series' => $series])
        </p>
        @if ($nextAction)
            <div class="mi-guide-note mi-guide-note-blue mt-3">
                <i class="fas fa-{{ $nextAction['icon'] }}"></i>
                <p>Next step: <strong>{{ $nextAction['label'] }}</strong> — open the {{ ucfirst($nextAction['tab']) }} tab.</p>
            </div>
        @elseif ($series->status === 'closed')
            <div class="mi-guide-note mi-guide-note-green mt-3">
                <i class="fas fa-flag-checkered"></i>
                <p>This quotation series is complete.</p>
            </div>
        @endif
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title"><i class="fas fa-list-check"></i> Quick stats</h3>
        <ul class="mi-guide-list">
            <li><strong>Line items</strong><span>{{ $series->items->count() }} products</span></li>
            <li><strong>Purchase type</strong><span>{{ ucfirst($series->purchase_type ?? 'local') }} · {{ $series->currency }}</span></li>
            @if ($series->isImport())
                <li><strong>Rates</strong><span>R = {{ number_format($series->exchange_rate, 2) }} · CBM = {{ $series->cbm_rate ? number_format($series->cbm_rate, 2) : '—' }}</span></li>
            @endif
            <li><strong>POs</strong><span>{{ $series->purchaseOrders->count() }} generated</span></li>
            <li><strong>Receipts</strong><span>{{ $series->goodsReceiptNotes->count() }} GRN(s)</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title"><i class="fas fa-diagram-project"></i> Workflow</h3>
        <div class="qs-timeline">
            @foreach ($workflowSteps ?? [] as $i => $step)
                @php
                    $stepIdx = array_search($step['key'], $statusOrder, true);
                    $isDone = $stepIdx !== false && $stepIdx < $currentIdx;
                    $isCurrent = $series->status === $step['key']
                        || ($step['key'] === 'received' && in_array($series->status, ['received', 'closed'], true));
                @endphp
                <div class="qs-timeline-item {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                    <div class="qs-timeline-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                    <div class="qs-timeline-body">
                        <p class="qs-timeline-title">{{ $step['label'] }}</p>
                        <p class="qs-timeline-desc">{{ $step['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    @if ($series->canExportQuotation() && $series->items->isNotEmpty())
        <section class="mi-guide-section">
            <h3 class="mi-guide-section-title"><i class="fas fa-file-export"></i> Export</h3>
            <p class="mi-guide-text">Send a blank-price draft to your supplier from the Quotation tab.</p>
            <div class="flex flex-wrap gap-2 mt-2">
                <a href="{{ route('quotation-series.export', [$series, 'csv']) }}" class="mi-btn-ghost text-xs"><i class="fas fa-file-csv"></i> CSV</a>
                <a href="{{ route('quotation-series.export', [$series, 'print']) }}" target="_blank" class="mi-btn-ghost text-xs"><i class="fas fa-print"></i> Print</a>
            </div>
        </section>
    @endif
</x-module.form-guide>
