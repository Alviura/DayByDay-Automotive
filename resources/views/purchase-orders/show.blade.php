@php
    $deliverySteps = [
        ['key' => 'sent', 'label' => 'PO Sent', 'icon' => 'fa-paper-plane'],
        ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-truck'],
        ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'fa-warehouse'],
        ['key' => 'received', 'label' => 'Received', 'icon' => 'fa-box-open'],
    ];
    $currentStepIdx = match (true) {
        $purchaseOrder->status === 'received' => 3,
        $purchaseOrder->status === 'partially_received' => 3,
        $purchaseOrder->delivery_status === 'delivered' => 2,
        $purchaseOrder->delivery_status === 'in_transit' => 1,
        in_array($purchaseOrder->status, ['sent', 'partially_received'], true) => 0,
        default => -1,
    };
    $receiptPct = $purchaseOrder->receiptProgressPercent();
    $lineCount = $purchaseOrder->items->count();
@endphp

<x-app-layout :title="$purchaseOrder->po_number">
    @push('styles')
        <x-module.page-index-styles />
        @include('purchase-orders.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $purchaseOrder->po_number }}</h1>
                        @include('purchase-orders.partials.status-badge', ['order' => $purchaseOrder])
                        @include('purchase-orders.partials.delivery-badge', ['order' => $purchaseOrder])
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span>{{ $purchaseOrder->supplier?->name }}</span>
                        @if ($purchaseOrder->quotationSeries)
                            <a href="{{ route('quotation-series.show', $purchaseOrder->quotationSeries) }}" class="mi-cat-badge hover:border-blue-300">
                                <i class="fas fa-folder-open text-[0.55rem]"></i> {{ $purchaseOrder->quotationSeries->displayName() }}
                            </a>
                        @endif
                        <span class="mi-cat-badge">{{ $purchaseOrder->currency }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchase-orders.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($purchaseOrder->canReceive())
                    @can('procurement.manage')
                        <a href="{{ route('goods-receipts.create', $purchaseOrder) }}" class="mi-btn-orange">
                            <i class="fas fa-truck-ramp-box text-xs"></i> Receive Goods
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Delivery workflow --}}
        @if ($purchaseOrder->status !== 'cancelled')
            <div class="mi-card p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Delivery Progress</p>
                <div class="po-delivery-track">
                    @foreach ($deliverySteps as $i => $step)
                        @php
                            $isDone = $currentStepIdx >= 0 && $i < $currentStepIdx;
                            $isCurrent = $currentStepIdx === $i
                                || ($step['key'] === 'received' && $purchaseOrder->status === 'partially_received' && $i === 3);
                        @endphp
                        <div class="po-delivery-step {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                            <div class="po-delivery-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                            <p class="po-delivery-label">{{ $step['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">PO Total</p>
                    <p class="mi-kpi-value orange">{{ number_format($purchaseOrder->total, 2) }}</p>
                    <p class="po-kpi-sub">{{ $purchaseOrder->currency }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Line Items</p>
                    <p class="mi-kpi-value">{{ $lineCount }}</p>
                    <p class="po-kpi-sub">{{ number_format($purchaseOrder->totalOrderedQuantity(), 0) }} units ordered</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Received</p>
                    <p class="mi-kpi-value">{{ $receiptPct }}%</p>
                    <p class="po-kpi-sub">{{ number_format($purchaseOrder->totalReceivedQuantity(), 0) }} of {{ number_format($purchaseOrder->totalOrderedQuantity(), 0) }} units</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-box-open"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Expected Date</p>
                    <p class="mi-kpi-value" style="font-size:1.1rem">{{ $purchaseOrder->expected_date?->format('d M Y') ?? '—' }}</p>
                    <p class="po-kpi-sub">Ordered {{ $purchaseOrder->order_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
        </div>

        {{-- Split layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5">

            {{-- Line items --}}
            <div class="mi-card">
                <div class="mi-card-head">
                    <div>
                        <p class="po-section-title"><i class="fas fa-boxes-stacked"></i> Line Items</p>
                        <p class="po-section-sub">Ordered quantities vs received</p>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Ordered</th>
                                <th>Received</th>
                                <th>Remaining</th>
                                <th>Progress</th>
                                <th>Unit Cost</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseOrder->items as $item)
                                @php
                                    $itemPct = $item->quantity > 0
                                        ? (int) round(($item->received_quantity / $item->quantity) * 100)
                                        : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="text-sm font-medium text-gray-800">{{ $item->product->part_number }}</span>
                                        <p class="mi-pkg-sub">{{ $item->product->name }}</p>
                                    </td>
                                    <td>{{ number_format($item->quantity, 2) }}</td>
                                    <td>{{ number_format($item->received_quantity, 2) }}</td>
                                    <td>{{ number_format($item->remainingQuantity(), 2) }}</td>
                                    <td>
                                        <div class="flex items-center gap-2 min-w-[4.5rem]">
                                            <div class="po-progress flex-1"><div class="po-progress-bar" style="width: {{ $itemPct }}%"></div></div>
                                            <span class="text-xs font-semibold text-gray-500">{{ $itemPct }}%</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                                    <td><span class="po-cost">{{ number_format($item->line_total, 2) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right font-semibold text-gray-600">Grand Total</td>
                                <td><span class="po-cost">{{ number_format($purchaseOrder->total, 2) }} {{ $purchaseOrder->currency }}</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">

                {{-- PO details --}}
                <div class="mi-card p-5 space-y-3">
                    <p class="po-section-title"><i class="fas fa-circle-info"></i> PO Details</p>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">PO Number</dt>
                            <dd class="font-semibold text-gray-800">{{ $purchaseOrder->po_number }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Supplier</dt>
                            <dd class="font-medium text-gray-800 text-right">{{ $purchaseOrder->supplier?->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Order Date</dt>
                            <dd class="text-gray-800">{{ $purchaseOrder->order_date?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Expected</dt>
                            <dd class="text-gray-800">{{ $purchaseOrder->expected_date?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-gray-500">Created By</dt>
                            <dd class="text-gray-800">{{ $purchaseOrder->creator?->name ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if ($purchaseOrder->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Notes</p>
                            <p class="text-sm text-gray-600">{{ $purchaseOrder->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Quotation series link --}}
                @if ($purchaseOrder->quotationSeries)
                    <div class="mi-card p-5">
                        <p class="po-section-title mb-3"><i class="fas fa-folder-open"></i> Source Series</p>
                        <a href="{{ route('quotation-series.show', $purchaseOrder->quotationSeries) }}" class="po-link-card">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="po-link-card-icon" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $purchaseOrder->quotationSeries->displayName() }}</p>
                                    <p class="text-xs text-gray-400">{{ $purchaseOrder->quotationSeries->series_number }}</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        </a>
                    </div>
                @endif

                {{-- Goods receipts --}}
                <div class="mi-card p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="po-section-title"><i class="fas fa-truck-ramp-box"></i> Goods Receipts</p>
                        @if ($purchaseOrder->canReceive())
                            @can('procurement.manage')
                                <a href="{{ route('goods-receipts.create', $purchaseOrder) }}" class="text-xs font-semibold text-orange-600 hover:text-orange-700">+ New GRN</a>
                            @endcan
                        @endif
                    </div>
                    @if ($purchaseOrder->goodsReceiptNotes->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($purchaseOrder->goodsReceiptNotes as $grn)
                                <a href="{{ route('goods-receipts.show', $grn) }}" class="po-link-card">
                                    <div class="flex items-center gap-3">
                                        <div class="po-link-card-icon" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">{{ $grn->grn_number }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ $grn->received_at?->format('d M Y') ?? '—' }}
                                                @if ($grn->warehouse) · {{ $grn->warehouse->name }} @endif
                                            </p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="po-empty-icon" style="width:2.5rem;height:2.5rem;font-size:.9rem"><i class="fas fa-box"></i></div>
                            <p class="text-xs text-gray-400">No receipts recorded yet</p>
                            @if ($purchaseOrder->canReceive())
                                @can('procurement.manage')
                                    <a href="{{ route('goods-receipts.create', $purchaseOrder) }}" class="mi-btn-orange mt-3 inline-flex text-xs py-2 px-3">
                                        <i class="fas fa-plus text-[0.6rem]"></i> Receive Goods
                                    </a>
                                @endcan
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
