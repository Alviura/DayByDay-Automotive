<x-app-layout :title="$return->return_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('returns.partials.page-styles')
    @endpush

    @php
        $sale = $return->sale;
        $workflow = [
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen'],
            ['key' => 'pending', 'label' => 'Approval', 'icon' => 'fa-hourglass-half'],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => 'fa-circle-check'],
        ];
        $estimatedRefund = $return->status === 'draft' ? $return->estimatedRefund() : (float) $return->refund_amount;
    @endphp

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-rotate-left"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $return->return_number }}</h1>
                        @include('returns.partials.status-badge', ['return' => $return])
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        @if ($sale)
                            <a href="{{ route('sales.show', $sale) }}" class="font-mono text-orange-600 hover:underline">{{ $sale->receipt_number }}</a>
                        @endif
                        <span>· {{ $return->shop?->name }}</span>
                        @if ($sale?->customerAccount)
                            <span>· {{ $sale->customerAccount->name }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('customer-returns.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($return->canSubmit())
                    @can('returns.create')
                        <form action="{{ route('customer-returns.submit', $return) }}" method="POST" class="inline"
                              data-confirm="Submit this return for approval?" data-confirm-variant="warning">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit</button>
                        </form>
                    @endcan
                @endif
                @if ($return->canDelete())
                    @can('returns.create')
                        <form action="{{ route('customer-returns.destroy', $return) }}" method="POST" class="inline"
                              data-confirm="Delete this draft return?" data-confirm-variant="danger">
                            @csrf @method('DELETE')
                            <button type="submit" class="mi-btn-ghost !text-red-600"><i class="fas fa-trash text-xs"></i> Delete</button>
                        </form>
                    @endcan
                @endif
                @if ($return->approval)
                    <a href="{{ route('approvals.show', $return->approval) }}" class="mi-btn-ghost">
                        <i class="fas fa-clipboard-check text-xs"></i> Approval
                    </a>
                @endif
            </div>
        </div>

        @if (! in_array($return->status, ['rejected'], true))
            <div class="mi-card p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Progress</p>
                <div class="rt-show-workflow">
                    @foreach ($workflow as $step)
                        @php
                            $isCurrent = $return->status === $step['key']
                                || ($step['key'] === 'pending' && $return->status === 'pending');
                            $isDone = match ($step['key']) {
                                'draft' => ! in_array($return->status, ['draft'], true),
                                'pending' => in_array($return->status, ['completed'], true) || ($return->status === 'pending' && $return->hasOpenApproval()),
                                'completed' => $return->status === 'completed',
                                default => false,
                            };
                        @endphp
                        <div class="rt-show-step {{ $isCurrent ? 'current' : '' }} {{ $isDone ? 'done' : '' }}">
                            <div class="rt-show-step-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                            <span class="rt-show-step-label">{{ $step['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($return->status === 'rejected')
            <div class="mi-card p-4 border-rose-100 bg-rose-50/50">
                <p class="text-sm text-rose-800"><i class="fas fa-circle-xmark mr-1"></i> This return was <strong>rejected</strong>. Revise and resubmit from draft when ready.</p>
            </div>
        @endif

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Line Items</p>
                    <p class="mi-kpi-value">{{ $return->items->count() }}</p>
                    <p class="rt-kpi-sub">{{ number_format($return->items->sum('quantity'), 2) }} units</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">{{ $return->status === 'completed' ? 'Refund' : 'Est. Refund' }}</p>
                    <p class="mi-kpi-value orange">{{ number_format($estimatedRefund, 2) }}</p>
                    <p class="rt-kpi-sub">KES {{ $return->status === 'completed' ? 'processed' : 'if approved' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Restock</p>
                    <p class="mi-kpi-value">{{ $return->items->where('restock', true)->count() }}</p>
                    <p class="rt-kpi-sub">lines marked for restock</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Created</p>
                    <p class="mi-kpi-value text-status" style="font-size:.95rem">{{ $return->created_at->format('d M Y') }}</p>
                    <p class="rt-kpi-sub">{{ $return->processor?->name ?? 'Not processed' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">
                <div class="mi-card p-5">
                    <p class="mi-field-label"><i class="fas fa-comment"></i> Reason</p>
                    <p class="text-sm text-gray-700 mt-2">{{ $return->reason }}</p>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Return lines</p>
                            <p class="text-xs text-gray-400 mt-0.5">Products being returned from the sale</p>
                        </div>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit price</th>
                                    <th>Condition</th>
                                    <th>Restock</th>
                                    <th>Refund</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($return->items as $item)
                                    <tr>
                                        <td>
                                            <p class="font-medium text-sm">{{ $item->product->part_number }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                        </td>
                                        <td class="font-semibold">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-sm text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->conditionLabel() }}</td>
                                        <td>
                                            @if ($item->restock)
                                                <span class="text-green-700 text-sm font-semibold">Yes</span>
                                            @else
                                                <span class="text-gray-400 text-sm">No</span>
                                            @endif
                                        </td>
                                        <td class="font-semibold text-orange-700">{{ number_format($item->lineRefund(), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50/80">
                                    <td colspan="5" class="text-right text-sm font-semibold text-gray-500 pr-4">Total refund</td>
                                    <td class="font-bold text-orange-700">{{ number_format($estimatedRefund, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if ($return->approval)
                    <div class="mi-card">
                        <div class="mi-card-head"><span class="text-sm font-semibold">Approval timeline</span></div>
                        <div class="px-6 py-5">
                            @include('partials.approval-timeline', ['approval' => $return->approval])
                        </div>
                    </div>
                @endif
            </div>

            <aside class="mi-guide">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Return info</h2>
                        <p class="mi-guide-subtitle">Linked sale & processing</p>
                    </div>
                </div>
                <dl class="mi-guide-meta">
                    @if ($sale)
                        <div>
                            <dt>Sale receipt</dt>
                            <dd><a href="{{ route('sales.show', $sale) }}" class="text-orange-600 hover:underline font-mono">{{ $sale->receipt_number }}</a></dd>
                        </div>
                        <div>
                            <dt>Sale type</dt>
                            <dd>{{ $sale->saleTypeLabel() }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>Shop</dt>
                        <dd>{{ $return->shop?->name ?? '—' }}</dd>
                    </div>
                    @if ($return->approved_at)
                        <div>
                            <dt>Approved</dt>
                            <dd>{{ $return->approved_at->format('d M Y H:i') }} · {{ $return->approver?->name }}</dd>
                        </div>
                    @endif
                    @if ($return->status === 'completed' && $return->processor)
                        <div>
                            <dt>Processed by</dt>
                            <dd>{{ $return->processor->name }}</dd>
                        </div>
                    @endif
                </dl>
            </aside>
        </div>
    </div>
</x-app-layout>
