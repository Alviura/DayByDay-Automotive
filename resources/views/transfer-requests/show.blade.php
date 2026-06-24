<x-app-layout :title="$transferRequest->request_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    @php
        $workflowSteps = [
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen'],
            ['key' => 'submitted', 'label' => 'Review', 'icon' => 'fa-hourglass-half'],
            ['key' => 'accepted', 'label' => 'Accepted', 'icon' => 'fa-circle-check'],
            ['key' => 'fulfilled', 'label' => 'Fulfilled', 'icon' => 'fa-flag-checkered'],
        ];
        $statusOrder = array_column($workflowSteps, 'key');
        $currentWorkflowKey = match ($transferRequest->status) {
            'draft' => 'draft',
            'submitted' => 'submitted',
            'accepted' => 'accepted',
            'fulfilled' => 'fulfilled',
            default => 'draft',
        };
        $workflowCurrentIndex = array_search($currentWorkflowKey, $statusOrder, true);
        if ($workflowCurrentIndex === false) {
            $workflowCurrentIndex = 0;
        }
        $totalUnits = $transferRequest->items->sum('requested_quantity');
    @endphp

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-inbox"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $transferRequest->request_number }}</h1>
                        @include('transfers.partials.status-badge', ['request' => $transferRequest])
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        @include('transfers.partials.route-display', ['transferRequest' => $transferRequest])
                        <span class="tr-type-pill">{{ $transferRequest->typeLabel() }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transfer-requests.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($transferRequest->canSubmit() && ($canManage ?? false))
                    <form action="{{ route('transfer-requests.submit', $transferRequest) }}" method="POST" class="inline"
                          data-confirm="Submit this request for review?" data-confirm-variant="warning">
                        @csrf
                        <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit</button>
                    </form>
                @endif
                @if ($transferRequest->canReview() && ($canReview ?? false))
                    <form action="{{ route('transfer-requests.accept', $transferRequest) }}" method="POST" class="inline"
                          data-confirm="Accept this request and create a stock transfer for administrator approval?"
                          data-confirm-variant="warning" data-confirm-label="Accept &amp; Issue">
                        @csrf
                        <button type="submit" class="mi-btn-orange"><i class="fas fa-check text-xs"></i> Accept &amp; Issue</button>
                    </form>
                    <form action="{{ route('transfer-requests.reject', $transferRequest) }}" method="POST" class="inline"
                          data-confirm="Reject this transfer request?" data-confirm-variant="danger">
                        @csrf
                        <button type="submit" class="mi-btn-ghost !text-red-600"><i class="fas fa-times text-xs"></i> Reject</button>
                    </form>
                @endif
                @if ($canCreateTransfer ?? false)
                    <form action="{{ route('transfer-requests.create-stock-transfer', $transferRequest) }}" method="POST" class="inline"
                          data-confirm="Create a stock transfer from this accepted request?" data-confirm-variant="warning">
                        @csrf
                        <button type="submit" class="mi-btn-orange"><i class="fas fa-right-left text-xs"></i> Create Stock Transfer</button>
                    </form>
                @endif
                @if ($transferRequest->stockTransfer)
                    <a href="{{ route('stock-transfers.show', $transferRequest->stockTransfer) }}" class="mi-btn-ghost">
                        Stock Transfer {{ $transferRequest->stockTransfer->transfer_number }}
                    </a>
                @endif
            </div>
        </div>

        @if (! in_array($transferRequest->status, ['rejected', 'cancelled'], true))
            <div class="mi-card p-4">
                <x-transfer.workflow-track
                    :steps="$workflowSteps"
                    :current-index="$workflowCurrentIndex"
                />
            </div>
        @endif

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Line Items</p>
                    <p class="mi-kpi-value">{{ $transferRequest->items->count() }}</p>
                    <p class="tr-kpi-sub">{{ number_format($totalUnits, 0) }} units requested</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Source</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ Str::limit($transferRequest->sourceLabel(), 16) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $transferRequest->isWarehouseSource() ? 'warehouse' : 'store' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Destination</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ Str::limit($transferRequest->destinationLabel(), 16) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-store"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Requested By</p>
                    <p class="mi-kpi-value text-status" style="font-size:.95rem">{{ $transferRequest->requester?->name ?? '—' }}</p>
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
                                    <th>Requested</th>
                                    @if (! empty($sourceAvailability))
                                        <th>Avail. at source</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transferRequest->items as $item)
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-sm">{{ $item->product->part_number }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                        </td>
                                        <td class="font-medium">{{ number_format($item->requested_quantity, 2) }}</td>
                                        @if (! empty($sourceAvailability))
                                            <td class="text-sm text-gray-600">{{ number_format($sourceAvailability[$item->product_id] ?? 0, 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($transferRequest->notes)
                    <div class="mi-card p-5">
                        <p class="mi-field-label"><i class="fas fa-note-sticky"></i> Notes</p>
                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $transferRequest->notes }}</p>
                    </div>
                @endif

                @if ($transferRequest->review_notes)
                    <div class="mi-card p-5">
                        <p class="mi-field-label"><i class="fas fa-comment"></i> Review Notes</p>
                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $transferRequest->review_notes }}</p>
                    </div>
                @endif
            </div>

            <aside class="mi-guide">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Request Info</h2>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <section class="mi-guide-section mi-guide-section-first">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-hashtag"></i> Request</span>
                                <span class="mi-show-meta-value mono">{{ $transferRequest->request_number }}</span>
                            </li>
                            @if ($transferRequest->reviewed_at)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-stamp"></i> Reviewed</span>
                                    <span class="mi-show-meta-value">{{ $transferRequest->reviewed_at->format('d M Y') }}</span>
                                    @if ($transferRequest->reviewer)
                                        <span class="mi-show-meta-sub">by {{ $transferRequest->reviewer->name }}</span>
                                    @endif
                                </li>
                            @endif
                            @if ($transferRequest->stockTransfer)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-right-left"></i> Stock Transfer</span>
                                    <a href="{{ route('stock-transfers.show', $transferRequest->stockTransfer) }}" class="mi-show-meta-value mono text-orange-600 hover:underline">
                                        {{ $transferRequest->stockTransfer->transfer_number }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </section>

                    @if ($transferRequest->status === 'draft' && ($canManage ?? false))
                        <section class="mi-guide-section">
                            <form action="{{ route('transfer-requests.destroy', $transferRequest) }}" method="POST"
                                  data-confirm="Delete this draft request?" data-confirm-variant="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mi-btn-ghost w-full justify-center !text-red-600 !border-red-100">
                                    <i class="fas fa-trash text-xs"></i> Delete Draft
                                </button>
                            </form>
                        </section>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
