<x-app-layout :title="$stockTransfer->transfer_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    @php
        $workflowSteps = [
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen'],
            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half'],
            ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check'],
            ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-truck'],
            ['key' => 'closed', 'label' => 'Completed', 'icon' => 'fa-flag-checkered'],
        ];
        $statusOrder = array_column($workflowSteps, 'key');
        $currentWorkflowKey = match ($stockTransfer->status) {
            'draft' => 'draft',
            'pending', 'returned' => 'pending',
            'approved' => 'approved',
            'dispatched', 'in_transit' => 'in_transit',
            'received', 'closed' => 'closed',
            default => 'draft',
        };
        $workflowCurrentIndex = array_search($currentWorkflowKey, $statusOrder, true);
        if ($workflowCurrentIndex === false) {
            $workflowCurrentIndex = 0;
        }
        $workflowAction = ($stockTransfer->status === 'approved' && ($canDispatch ?? false) && auth()->user()->can('transfers.dispatch'))
            ? [
                'step' => 'in_transit',
                'url' => route('stock-transfers.dispatch', $stockTransfer),
                'confirm' => 'Mark this transfer as in transit? Stock will leave the source location.',
                'confirmLabel' => 'Mark In Transit',
            ]
            : null;
        if ($workflowAction) {
            $workflowCurrentIndex = array_search('in_transit', $statusOrder, true);
        }
        $totalUnits = $stockTransfer->items->sum('quantity');
    @endphp

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $stockTransfer->transfer_number }}</h1>
                        <span class="{{ $stockTransfer->statusBadgeClass() }}">{{ $stockTransfer->statusLabel() }}</span>
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        <span>{{ $stockTransfer->routeLabel() }}</span>
                        <span class="tr-type-pill">{{ $stockTransfer->typeLabel() }}</span>
                        @if ($stockTransfer->transferRequest)
                            <a href="{{ route('transfer-requests.show', $stockTransfer->transferRequest) }}" class="font-mono text-xs text-orange-600 hover:underline">
                                {{ $stockTransfer->transferRequest->request_number }}
                            </a>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stock-transfers.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($stockTransfer->canSubmit() && ($canManage ?? false))
                    @can('transfers.create')
                        <form action="{{ route('stock-transfers.submit', $stockTransfer) }}" method="POST" class="inline"
                              data-confirm="Submit this transfer for administrator approval?" data-confirm-variant="warning">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit for Approval</button>
                        </form>
                    @endcan
                @endif
                @if ($stockTransfer->canDispatch() && ($canDispatch ?? false))
                    @can('transfers.dispatch')
                        <form action="{{ route('stock-transfers.dispatch', $stockTransfer) }}" method="POST" class="inline"
                              data-confirm="Mark this transfer as in transit? Stock will leave the source location."
                              data-confirm-variant="warning" data-confirm-label="Mark In Transit">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-truck text-xs"></i> Mark In Transit</button>
                        </form>
                    @endcan
                @endif
                @if ($stockTransfer->canReceive() && ($canReceive ?? false))
                    @can('transfers.receive')
                        <a href="{{ route('stock-transfers.receive', $stockTransfer) }}" class="mi-btn-orange">
                            <i class="fas fa-truck-ramp-box text-xs"></i> Receive
                        </a>
                    @endcan
                @endif
                @if ($stockTransfer->approval)
                    @can('approvals.act')
                        <a href="{{ route('approvals.show', $stockTransfer->approval) }}" class="mi-btn-ghost">Approval</a>
                    @endcan
                @endif
            </div>
        </div>

        @if ($stockTransfer->transferRequest && in_array($stockTransfer->status, ['draft', 'returned'], true) && ($canManage ?? false))
            <div class="tr-banner tr-banner-info no-print">
                <i class="fas fa-circle-info"></i>
                <span>
                    Issuance from request <strong>{{ $stockTransfer->transferRequest->request_number }}</strong> —
                    submit for administrator approval before stock can be dispatched.
                </span>
            </div>
        @endif

        @if ($stockTransfer->canDispatch() && ($canDispatch ?? false))
            <div class="tr-banner tr-banner-info no-print !border-orange-200 !bg-orange-50/60">
                <i class="fas fa-truck text-orange-500"></i>
                <span>
                    <strong>Approved for release.</strong> Mark as in transit when stock leaves {{ $stockTransfer->sourceLabel() }}.
                </span>
            </div>
        @elseif (($isReadOnlyInbound ?? false) && $stockTransfer->status === 'approved')
            <div class="tr-banner tr-banner-info no-print">
                <i class="fas fa-circle-info"></i>
                <span>
                    Approved — awaiting dispatch from <strong>{{ $stockTransfer->sourceLabel() }}</strong>.
                    You can receive once it is marked in transit.
                </span>
            </div>
        @elseif ($isReadOnlyInbound ?? false)
            <div class="tr-banner tr-banner-info no-print">
                <i class="fas fa-circle-info"></i>
                <span>Inbound transfer to your shop — receive when stock arrives.</span>
            </div>
        @endif

        @if (! in_array($stockTransfer->status, ['rejected', 'cancelled'], true))
            <div class="mi-card p-4">
                <x-transfer.workflow-track
                    :steps="$workflowSteps"
                    :current-index="$workflowCurrentIndex"
                    :action="$workflowAction"
                />
            </div>
        @endif

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Line Items</p>
                    <p class="mi-kpi-value">{{ $stockTransfer->items->count() }}</p>
                    <p class="tr-kpi-sub">{{ number_format($totalUnits, 0) }} units</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Source</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ Str::limit($stockTransfer->sourceLabel(), 16) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $stockTransfer->isWarehouseSource() ? 'warehouse' : 'store' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Destination</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ Str::limit($stockTransfer->destinationLabel(), 16) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $stockTransfer->isWarehouseDestination() ? 'warehouse' : 'store' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Created By</p>
                    <p class="mi-kpi-value text-status" style="font-size:.95rem">{{ $stockTransfer->creator?->name ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user"></i></div>
            </div>
        </div>

        <div class="tr-show-grid">
            <div class="space-y-5">
                <div class="mi-card">
                    <div class="mi-card-head">
                        <p class="text-sm font-semibold text-gray-800">Line items</p>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    @if ($stockTransfer->status !== 'draft')
                                        <th>Dispatched</th>
                                        <th>Received</th>
                                        <th>Good</th>
                                    @elseif (! empty($sourceAvailability))
                                        <th>Avail. now</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockTransfer->items as $item)
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-sm">{{ $item->product->part_number }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                        </td>
                                        <td class="font-medium">{{ number_format($item->quantity, 2) }}</td>
                                        @if ($stockTransfer->status !== 'draft')
                                            <td>{{ number_format($item->dispatched_quantity, 2) }}</td>
                                            <td>{{ number_format($item->received_quantity, 2) }}</td>
                                            <td class="text-green-700 font-medium">{{ number_format($item->goodQuantity(), 2) }}</td>
                                        @elseif (! empty($sourceAvailability))
                                            <td class="text-sm text-gray-600">{{ number_format($sourceAvailability[$item->product_id] ?? 0, 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($stockTransfer->notes)
                    <div class="mi-card p-5">
                        <p class="mi-field-label"><i class="fas fa-note-sticky"></i> Notes</p>
                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $stockTransfer->notes }}</p>
                    </div>
                @endif
            </div>

            <aside class="mi-guide">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Transfer Info</h2>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <section class="mi-guide-section mi-guide-section-first">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-hashtag"></i> Transfer</span>
                                <span class="mi-show-meta-value mono">{{ $stockTransfer->transfer_number }}</span>
                            </li>
                            @if ($stockTransfer->approved_at)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-stamp"></i> Approved</span>
                                    <span class="mi-show-meta-value">{{ $stockTransfer->approved_at->format('d M Y') }}</span>
                                </li>
                            @endif
                            @if ($stockTransfer->dispatched_at)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-truck"></i> Dispatched</span>
                                    <span class="mi-show-meta-value">{{ $stockTransfer->dispatched_at->format('d M Y H:i') }}</span>
                                </li>
                            @endif
                            @if ($stockTransfer->received_at)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-check"></i> Received</span>
                                    <span class="mi-show-meta-value">{{ $stockTransfer->received_at->format('d M Y H:i') }}</span>
                                </li>
                            @endif
                        </ul>
                    </section>

                    @if ($stockTransfer->status === 'draft' && ($canManage ?? false))
                        @can('transfers.create')
                            <section class="mi-guide-section">
                                <form action="{{ route('stock-transfers.destroy', $stockTransfer) }}" method="POST"
                                      data-confirm="Delete this draft transfer?" data-confirm-variant="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mi-btn-ghost w-full justify-center !text-red-600 !border-red-100">
                                        <i class="fas fa-trash text-xs"></i> Delete Draft
                                    </button>
                                </form>
                            </section>
                        @endcan
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
