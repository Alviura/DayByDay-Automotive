<x-app-layout :title="$stockAdjustment->adjustment_number">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-sliders"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900">{{ $stockAdjustment->adjustment_number }}</h1>
                        <span class="mi-status-{{ $stockAdjustment->status === 'approved' ? 'active' : ($stockAdjustment->status === 'pending' ? 'pending' : ($stockAdjustment->status === 'rejected' ? 'rejected' : 'inactive')) }}">
                            {{ $stockAdjustment->statusLabel() }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $stockAdjustment->locationLabel() }} · {{ $stockAdjustment->reasonLabel() }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stock-adjustments.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($stockAdjustment->canSubmit())
                    @can('inventory.adjust')
                        <form action="{{ route('stock-adjustments.submit', $stockAdjustment) }}" method="POST" class="inline"
                              onsubmit="return confirm('Submit this adjustment for approval?');">
                            @csrf
                            <button type="submit" class="mi-btn-orange">
                                <i class="fas fa-paper-plane text-xs"></i> Submit for Approval
                            </button>
                        </form>
                    @endcan
                @endif
                @if ($stockAdjustment->approval && $stockAdjustment->status === 'pending')
                    <a href="{{ route('approvals.show', $stockAdjustment->approval) }}" class="mi-btn-ghost">
                        <i class="fas fa-clipboard-check text-xs"></i> View Approval
                    </a>
                @endif
            </div>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">
                <div class="mi-card">
                    <div class="mi-card-head"><span class="text-sm font-semibold">Line Items</span></div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>System Qty</th>
                                    <th>Counted Qty</th>
                                    <th>Difference</th>
                                    <th>Unit Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockAdjustment->items as $item)
                                    <tr>
                                        <td>
                                            <p class="mi-pkg-name">{{ $item->product->part_number }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                        </td>
                                        <td>{{ number_format($item->system_quantity, 2) }}</td>
                                        <td>{{ number_format($item->counted_quantity, 2) }}</td>
                                        <td class="font-semibold {{ $item->difference < 0 ? 'text-red-600' : ($item->difference > 0 ? 'text-green-600' : 'text-gray-500') }}">
                                            {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference, 2) }}
                                        </td>
                                        <td>{{ number_format($item->unit_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($stockAdjustment->notes)
                    <div class="mi-card p-5">
                        <p class="mi-field-label"><i class="fas fa-note-sticky"></i> Notes</p>
                        <p class="text-sm text-gray-700 mt-2">{{ $stockAdjustment->notes }}</p>
                    </div>
                @endif

                @if ($stockAdjustment->approval)
                    <div class="mi-card">
                        <div class="mi-card-head"><span class="text-sm font-semibold">Approval Timeline</span></div>
                        <div class="px-6 py-5">
                            @include('partials.approval-timeline', ['approval' => $stockAdjustment->approval])
                        </div>
                    </div>
                @endif
            </div>

            <aside class="mi-guide">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Adjustment Info</h2>
                        <p class="mi-guide-subtitle">Workflow & metadata</p>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <ul class="mi-show-meta">
                        <li>
                            <span class="mi-show-meta-label">Created by</span>
                            <span class="mi-show-meta-value">{{ $stockAdjustment->creator?->name ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="mi-show-meta-label">Created</span>
                            <span class="mi-show-meta-value">{{ $stockAdjustment->created_at->format('d M Y H:i') }}</span>
                        </li>
                        @if ($stockAdjustment->approved_at)
                            <li>
                                <span class="mi-show-meta-label">Approved</span>
                                <span class="mi-show-meta-value">{{ $stockAdjustment->approved_at->format('d M Y H:i') }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label">Approved by</span>
                                <span class="mi-show-meta-value">{{ $stockAdjustment->approver?->name ?? '—' }}</span>
                            </li>
                        @endif
                    </ul>

                    @if ($stockAdjustment->status === 'draft')
                        @can('inventory.adjust')
                            <form action="{{ route('stock-adjustments.destroy', $stockAdjustment) }}" method="POST" class="mt-4"
                                  onsubmit="return confirm('Delete this draft?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mi-btn-danger w-full justify-center">
                                    <i class="fas fa-trash text-xs"></i> Delete Draft
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
