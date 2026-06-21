@php
    $statusOrder = ['quotation_draft', 'order_draft', 'approved', 'po_generated', 'in_transit', 'received', 'closed'];
    $currentIdx = array_search($series->status, $statusOrder, true);
    if ($currentIdx === false) {
        $currentIdx = 0;
    }
    $workflowSteps = [
        ['key' => 'quotation_draft', 'label' => 'Quotation', 'icon' => 'fa-pen', 'desc' => 'Add products & export draft'],
        ['key' => 'order_draft', 'label' => 'Order', 'icon' => 'fa-calculator', 'desc' => 'Enter prices & calculate'],
        ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'desc' => 'Order confirmed'],
        ['key' => 'po_generated', 'label' => 'PO Sent', 'icon' => 'fa-file-invoice', 'desc' => 'Purchase order generated'],
        ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-truck', 'desc' => 'Goods on the way'],
        ['key' => 'received', 'label' => 'Received', 'icon' => 'fa-box-open', 'desc' => 'Goods received'],
        ['key' => 'closed', 'label' => 'Closed', 'icon' => 'fa-flag-checkered', 'desc' => 'Series completed'],
    ];
    $defaultTab = match ($series->status) {
        'order_draft', 'cost_analysis', 'pending_approval' => 'order',
        'approved', 'po_generated', 'in_transit', 'received', 'closed' => 'workflow',
        default => 'quotation',
    };
    $itemCount = $series->items->count();
    $currencyLabel = $series->isImport() ? $series->currency : 'KES';
@endphp

<x-app-layout :title="$series->displayName()">
    @push('styles')
        <x-module.page-index-styles />
        @include('quotation-series.partials.show-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ tab: '{{ $defaultTab }}' }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $series->displayName() }}</h1>
                        @include('quotation-series.partials.status-badge', ['series' => $series])
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge"><i class="fas fa-hashtag text-[0.55rem]"></i> {{ $series->series_number }}</span>
                        <span>{{ $series->supplier?->name }}</span>
                        <span class="mi-cat-badge {{ $series->isImport() ? 'qs-type-import' : 'qs-type-local' }}">{{ ucfirst($series->purchase_type ?? 'local') }}</span>
                        <span>{{ $series->currency }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('quotation-series.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($series->canEditHeader())
                    @can('procurement.manage')
                        <a href="{{ route('quotation-series.edit', $series) }}" class="mi-btn-ghost"><i class="fas fa-pen text-xs"></i> Edit</a>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Workflow progress --}}
        <div class="mi-card p-4">
            <div class="qs-workflow-track">
                @foreach ($workflowSteps as $i => $step)
                    @php
                        $stepIdx = array_search($step['key'], $statusOrder, true);
                        $isDone = $stepIdx !== false && $stepIdx < $currentIdx;
                        $isCurrent = $series->status === $step['key']
                            || ($step['key'] === 'received' && $series->status === 'received')
                            || ($step['key'] === 'closed' && $series->status === 'closed');
                    @endphp
                    <div class="qs-workflow-step {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="qs-workflow-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                        <p class="qs-workflow-label">{{ $step['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Purchase Total</p>
                    <p class="mi-kpi-value">{{ number_format($series->total_purchase_price, 2) }}</p>
                    <p class="qs-show-kpi-sub">{{ $currencyLabel }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Transport</p>
                    <p class="mi-kpi-value">{{ number_format($series->total_transport_cost, 2) }}</p>
                    <p class="qs-show-kpi-sub">KES</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-ship"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Actual Cost</p>
                    <p class="mi-kpi-value orange">{{ number_format($series->total_actual_cost, 2) }}</p>
                    <p class="qs-show-kpi-sub">KES</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Expected Margin</p>
                    <p class="mi-kpi-value">{{ number_format($series->total_expected_margin, 2) }}</p>
                    <p class="qs-show-kpi-sub">KES</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mi-tab-bar">
            <button type="button" @click="tab = 'overview'" :class="{ 'active': tab === 'overview' }">
                <i class="fas fa-circle-info"></i> Overview
            </button>
            <button type="button" @click="tab = 'quotation'" :class="{ 'active': tab === 'quotation' }">
                <i class="fas fa-list"></i> Quotation
                @if ($itemCount > 0)<span class="qs-tab-badge">{{ $itemCount }}</span>@endif
            </button>
            <button type="button" @click="tab = 'order'" :class="{ 'active': tab === 'order' }">
                <i class="fas fa-calculator"></i> Order Processing
            </button>
            <button type="button" @click="tab = 'workflow'" :class="{ 'active': tab === 'workflow' }">
                <i class="fas fa-diagram-project"></i> Workflow
                @if ($series->purchaseOrders->isNotEmpty())<span class="qs-tab-badge">{{ $series->purchaseOrders->count() }}</span>@endif
            </button>
        </div>

        {{-- Tab panels --}}
        <div x-show="tab === 'overview'" x-cloak>
            @include('quotation-series.partials.overview-tab', ['workflowSteps' => $workflowSteps])
        </div>

        <div x-show="tab === 'quotation'" x-cloak class="space-y-4">
            <div class="qs-phase-banner qs-phase-banner-violet">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Phase 2 — Quotation Draft.</strong>
                    Bulk-select products and quantities, then export a blank-price draft to send your supplier.
                    @if ($series->canProceedToOrder())
                        When ready, click <strong>Start Order Processing</strong> at the bottom.
                    @endif
                </div>
            </div>
            @include('quotation-series.partials.quotation-tab')
        </div>

        <div x-show="tab === 'order'" x-cloak class="space-y-4">
            <div class="qs-phase-banner qs-phase-banner-amber">
                <i class="fas fa-calculator"></i>
                <div>
                    <strong>Phase 3 — Order Processing.</strong>
                    Enter supplier prices{{ $series->isImport() ? ', dimensions, and packet counts' : ' and transport costs' }},
                    then calculate margins against MKT wholesale prices.
                </div>
            </div>
            @include('quotation-series.partials.order-tab')
        </div>

        <div x-show="tab === 'workflow'" x-cloak class="space-y-4">
            <div class="qs-phase-banner qs-phase-banner-blue">
                <i class="fas fa-diagram-project"></i>
                <div>
                    <strong>Post-approval workflow.</strong>
                    Generate purchase orders, mark in transit, receive goods, and close the series when complete.
                </div>
            </div>
            @include('quotation-series.partials.workflow-tab', ['workflowSteps' => $workflowSteps])
        </div>
    </div>
</x-app-layout>
