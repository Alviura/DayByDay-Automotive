<x-app-layout :title="$transfer->request_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    @php
        $st = $transfer->stockTransfer;
        $workflow = [
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'done' => ! in_array($transfer->status, ['draft'], true)],
            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'done' => in_array($transfer->status, ['approved', 'dispatched', 'completed'], true)],
            ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'done' => in_array($transfer->status, ['dispatched', 'completed'], true)],
            ['key' => 'dispatched', 'label' => 'In Transit', 'icon' => 'fa-truck', 'done' => $transfer->status === 'completed'],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => 'fa-flag-checkered', 'done' => $transfer->status === 'completed'],
        ];
        $totalUnits = $transfer->items->sum('requested_quantity');
    @endphp

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $transfer->request_number }}</h1>
                        @include('transfers.partials.status-badge', ['request' => $transfer])
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        @include('transfers.partials.route-display', ['transferRequest' => $transfer])
                        <span class="tr-type-pill">{{ $transfer->typeLabel() }}</span>
                        @if ($st)
                            <span class="font-mono text-xs text-gray-400">{{ $st->transfer_number }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transfers.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($transfer->canSubmit() && ($canManage ?? true))
                    @can('transfers.create')
                        <form action="{{ route('transfers.submit', $transfer) }}" method="POST" class="inline" data-confirm="Submit this transfer for approval?" data-confirm-variant="warning">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit</button>
                        </form>
                    @endcan
                @endif
                @if ($transfer->canDispatch())
                    @if ($canDispatch ?? true)
                        @can('transfers.dispatch')
                            <form action="{{ route('transfers.dispatch', $transfer) }}" method="POST" class="inline"
                                  data-confirm="Dispatch stock from source? Quantities will leave the source location."
                                  data-confirm-variant="warning" data-confirm-label="Dispatch">
                                @csrf
                                <button type="submit" class="mi-btn-orange"><i class="fas fa-truck text-xs"></i> Dispatch</button>
                            </form>
                        @endcan
                    @endif
                @endif
                @if ($st && $st->canReceive() && ($canReceive ?? true))
                    @can('transfers.receive')
                        <a href="{{ route('transfers.receive', $transfer) }}" class="mi-btn-orange">
                            <i class="fas fa-truck-ramp-box text-xs"></i> Receive
                        </a>
                    @endcan
                @endif
                @if ($transfer->approval)
                    @can('approvals.act')
                        <a href="{{ route('approvals.show', $transfer->approval) }}" class="mi-btn-ghost">Approval</a>
                    @endcan
                @endif
            </div>
        </div>

        @if ($isReadOnlyInbound ?? false)
            <div class="tr-banner tr-banner-info no-print">
                <i class="fas fa-circle-info"></i>
                <span>
                    @if ($transfer->type === 'shop_to_warehouse')
                        Shop return to your warehouse — view and receive only. Created by the shop and approved by administrators.
                    @else
                        Warehouse distribution to your shop — view and receive only. Created and approved by administrators.
                    @endif
                </span>
            </div>
        @endif

        {{-- Workflow --}}
        @if (! in_array($transfer->status, ['rejected', 'cancelled'], true))
            <div class="mi-card p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Progress</p>
                <div class="tr-show-workflow">
                    @foreach ($workflow as $step)
                        @php
                            $isCurrent = $transfer->status === $step['key']
                                || ($step['key'] === 'pending' && $transfer->status === 'returned');
                            $isDone = $step['done'] || $isCurrent && $transfer->status === 'completed';
                        @endphp
                        <div class="tr-show-step {{ $isCurrent ? 'current' : '' }} {{ $isDone ? 'done' : '' }}">
                            <div class="tr-show-step-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                            <span class="tr-show-step-label">{{ $step['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Line Items</p>
                    <p class="mi-kpi-value">{{ $transfer->items->count() }}</p>
                    <p class="tr-kpi-sub">{{ number_format($totalUnits, 0) }} units requested</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Source</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ Str::limit($transfer->sourceLabel(), 16) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $transfer->isWarehouseSource() ? 'warehouse' : 'store' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Destination</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ Str::limit($transfer->destinationLabel(), 16) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $transfer->isWarehouseDestination() ? 'warehouse' : 'store' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Requested By</p>
                    <p class="mi-kpi-value text-status" style="font-size:.95rem">{{ $transfer->requester?->name ?? '—' }}</p>
                    <p class="tr-kpi-sub">{{ $transfer->created_at->format('d M Y') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user"></i></div>
            </div>
        </div>

        <div class="tr-show-grid">
            <div class="space-y-5">
                {{-- Lines --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Line items</p>
                            <p class="text-xs text-gray-400 mt-0.5">Requested → approved → dispatched → received</p>
                        </div>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Requested</th>
                                    @if ($transfer->status !== 'draft')
                                        <th>Approved</th>
                                    @endif
                                    @if ($st)
                                        <th>Dispatched</th>
                                        <th>Received</th>
                                        <th>Good</th>
                                    @elseif (! empty($sourceAvailability))
                                        <th>Avail. now</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transfer->items as $item)
                                    @php $ti = $st?->items->firstWhere('product_id', $item->product_id); @endphp
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-sm">{{ $item->product->part_number }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                        </td>
                                        <td class="font-medium">{{ number_format($item->requested_quantity, 2) }}</td>
                                        @if ($transfer->status !== 'draft')
                                            <td>{{ $item->approved_quantity ? number_format($item->approved_quantity, 2) : '—' }}</td>
                                        @endif
                                        @if ($st)
                                            <td>{{ $ti ? number_format($ti->dispatched_quantity, 2) : '—' }}</td>
                                            <td>{{ $ti ? number_format($ti->received_quantity, 2) : '—' }}</td>
                                            <td class="text-green-700 font-medium">{{ $ti ? number_format($ti->goodQuantity(), 2) : '—' }}</td>
                                        @elseif (! empty($sourceAvailability))
                                            <td class="text-sm {{ ($sourceAvailability[$item->product_id] ?? 0) < $item->requested_quantity ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                {{ number_format($sourceAvailability[$item->product_id] ?? 0, 2) }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($transfer->notes)
                    <div class="mi-card p-5">
                        <p class="mi-field-label"><i class="fas fa-note-sticky"></i> Notes</p>
                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $transfer->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <aside class="mi-guide">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Transfer Info</h2>
                        <p class="mi-guide-subtitle">Record &amp; logistics</p>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <section class="mi-guide-section mi-guide-section-first">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-hashtag"></i> Request</span>
                                <span class="mi-show-meta-value mono">{{ $transfer->request_number }}</span>
                            </li>
                            @if ($st)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-truck"></i> Dispatch</span>
                                    <span class="mi-show-meta-value mono">{{ $st->transfer_number }}</span>
                                </li>
                                @if ($st->dispatched_at)
                                    <li>
                                        <span class="mi-show-meta-label"><i class="fas fa-calendar"></i> Dispatched</span>
                                        <span class="mi-show-meta-value">{{ $st->dispatched_at->format('d M Y H:i') }}</span>
                                        @if ($st->dispatcher)
                                            <span class="mi-show-meta-sub">by {{ $st->dispatcher->name }}</span>
                                        @endif
                                    </li>
                                @endif
                                @if ($st->received_at)
                                    <li>
                                        <span class="mi-show-meta-label"><i class="fas fa-check"></i> Received</span>
                                        <span class="mi-show-meta-value">{{ $st->received_at->format('d M Y H:i') }}</span>
                                        @if ($st->receiver)
                                            <span class="mi-show-meta-sub">by {{ $st->receiver->name }}</span>
                                        @endif
                                    </li>
                                @endif
                            @endif
                            @if ($transfer->approved_at)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-stamp"></i> Approved</span>
                                    <span class="mi-show-meta-value">{{ $transfer->approved_at->format('d M Y') }}</span>
                                </li>
                            @endif
                        </ul>
                    </section>

                    @if ($transfer->status === 'draft' && ($canManage ?? true))
                        @can('transfers.create')
                            <section class="mi-guide-section">
                                <form action="{{ route('transfers.destroy', $transfer) }}" method="POST"
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
