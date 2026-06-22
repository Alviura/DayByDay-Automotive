<x-app-layout :title="'Approval #'.$approval->id">

    @push('styles')
        <x-module.page-index-styles />
        @include('approvals.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $approval->documentTitle() }}</h1>
                        <span class="{{ $approval->status->badgeClass() }}">{{ $approval->status->label() }}</span>
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge">
                            <i class="fas {{ $approval->moduleIcon() }} text-[0.55rem]"></i>
                            {{ $approval->moduleLabel() }}
                        </span>
                        <span class="mi-cat-badge">
                            <i class="fas fa-hashtag text-[0.55rem]"></i>
                            {{ $approval->documentReference() }}
                        </span>
                    </p>
                </div>
            </div>
            <a href="{{ route('approvals.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i>
                Back to Inbox
            </a>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Requester</p>
                    <p class="mi-kpi-value text-status">{{ $approval->requester?->name ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Current Approver</p>
                    <p class="mi-kpi-value text-status">{{ $approval->currentApprover?->name ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Submitted</p>
                    <p class="mi-kpi-value text-status">{{ $approval->created_at->format('d M Y') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar-plus"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Completed</p>
                    <p class="mi-kpi-value text-status">{{ $approval->completed_at?->format('d M Y') ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">

                @include('approvals.partials.document-preview', ['approval' => $approval])

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-file-lines text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Document Summary</span>
                        </div>
                    </div>
                    <dl class="mi-detail-grid">
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-layer-group"></i> Module</dt>
                            <dd class="mi-detail-value">{{ $approval->moduleLabel() }}</dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Reference</dt>
                            <dd class="mi-detail-value"><span class="mi-cat-badge">{{ $approval->documentReference() }}</span></dd>
                        </div>
                        <div class="mi-detail-item mi-span-full">
                            <dt class="mi-detail-label"><i class="fas fa-align-left"></i> Summary</dt>
                            <dd class="mi-detail-value">{{ $approval->documentSummary() }}</dd>
                        </div>
                        @if ($approval->approvable instanceof \App\Models\StockAdjustment)
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-sliders"></i> Source Document</dt>
                                <dd class="mi-detail-value">
                                    <a href="{{ route('stock-adjustments.show', $approval->approvable) }}" class="text-orange-600 hover:text-orange-700">
                                        View adjustment {{ $approval->approvable->adjustment_number }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if ($approval->approvable instanceof \App\Models\QuotationSeries)
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-folder-open"></i> Source Document</dt>
                                <dd class="mi-detail-value">
                                    <a href="{{ route('quotation-series.show', $approval->approvable) }}" class="text-orange-600 hover:text-orange-700">
                                        View series {{ $approval->approvable->displayName() }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if ($approval->approvable instanceof \App\Models\TransferRequest)
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-right-left"></i> Source Document</dt>
                                <dd class="mi-detail-value">
                                    <a href="{{ route('transfers.show', $approval->approvable) }}" class="text-orange-600 hover:text-orange-700">
                                        View transfer {{ $approval->approvable->request_number }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if ($approval->approvable instanceof \App\Models\ReturnRecord)
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-rotate-left"></i> Source Document</dt>
                                <dd class="mi-detail-value">
                                    @if ($approval->approvable->type === 'customer')
                                        <a href="{{ route('customer-returns.show', $approval->approvable) }}" class="text-orange-600 hover:text-orange-700">
                                            View return {{ $approval->approvable->return_number }}
                                        </a>
                                    @else
                                        <a href="{{ route('supplier-returns.show', $approval->approvable) }}" class="text-orange-600 hover:text-orange-700">
                                            View return {{ $approval->approvable->return_number }}
                                        </a>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($approval->notes)
                            <div class="mi-detail-item mi-span-full">
                                <dt class="mi-detail-label"><i class="fas fa-note-sticky"></i> Submission Notes</dt>
                                <dd class="mi-detail-value">{{ $approval->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-clock-rotate-left text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Approval Timeline</span>
                        </div>
                    </div>
                    <div class="px-6 py-5">
                        @include('partials.approval-timeline', ['approval' => $approval])
                    </div>
                </div>

                @if ($canAct)
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-gavel text-gray-400 text-sm"></i>
                                <span class="text-sm font-semibold">Take Action</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('approvals.act', $approval) }}" class="p-6 space-y-4"
                              x-data="{ action: 'approved' }"
                              x-on:submit="if (action === 'rejected' && !window.appConfirm('Reject this request? The document will not proceed.', { variant: 'danger', confirmLabel: 'Reject' })) { $event.preventDefault(); } if (action === 'returned' && !window.appConfirm('Return this request for revision?', { variant: 'warning', confirmLabel: 'Return' })) { $event.preventDefault(); }">
                            @csrf
                            <div>
                                <label class="mi-field-label"><i class="fas fa-list-check"></i> Decision</label>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <label class="mi-approval-action-option" :class="{ 'active': action === 'approved' }">
                                        <input type="radio" name="action" value="approved" x-model="action" class="sr-only">
                                        <i class="fas fa-circle-check"></i> Approve
                                    </label>
                                    <label class="mi-approval-action-option" :class="{ 'active': action === 'returned' }">
                                        <input type="radio" name="action" value="returned" x-model="action" class="sr-only">
                                        <i class="fas fa-rotate-left"></i> Return
                                    </label>
                                    <label class="mi-approval-action-option" :class="{ 'active': action === 'rejected' }">
                                        <input type="radio" name="action" value="rejected" x-model="action" class="sr-only">
                                        <i class="fas fa-circle-xmark"></i> Reject
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('action')" class="mt-1.5" />
                            </div>
                            <div>
                                <label for="comments" class="mi-field-label">
                                    <i class="fas fa-comment"></i> Comments
                                    <span class="text-gray-400 font-normal" x-show="action !== 'approved'">(required)</span>
                                </label>
                                <textarea id="comments" name="comments" rows="3" class="mi-input block w-full resize-y"
                                          placeholder="Add context for your decision…">{{ old('comments') }}</textarea>
                                <x-input-error :messages="$errors->get('comments')" class="mt-1.5" />
                            </div>
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('approvals.index') }}" class="mi-btn-ghost">Cancel</a>
                                <button type="submit" class="mi-btn-orange">
                                    <i class="fas fa-check text-xs"></i>
                                    Submit Decision
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <aside class="mi-guide">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon">
                        <i class="fas fa-circle-info"></i>
                    </div>
                    <div>
                        <h2 class="mi-guide-title">Approval Info</h2>
                        <p class="mi-guide-subtitle">Request metadata</p>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <section class="mi-guide-section mi-guide-section-first">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-fingerprint"></i> Approval ID</span>
                                <span class="mi-show-meta-value mono">#{{ $approval->id }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-toggle-on"></i> Status</span>
                                <span class="mi-show-meta-value">{{ $approval->status->label() }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-user"></i> Requested by</span>
                                <span class="mi-show-meta-value">{{ $approval->requester?->name ?? '—' }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-user-check"></i> Assigned to</span>
                                <span class="mi-show-meta-value">{{ $approval->currentApprover?->name ?? '—' }}</span>
                            </li>
                        </ul>
                    </section>

                    <section class="mi-guide-section">
                        <h3 class="mi-guide-section-title"><i class="fas fa-lightbulb"></i> Guidelines</h3>
                        <ul class="mi-guide-tips">
                            <li><i class="fas fa-check"></i> Review the line preview above before deciding — it shows quantities, routes, and amounts.</li>
                            <li><i class="fas fa-check"></i> Approve when the request meets policy and supporting details are complete.</li>
                            <li><i class="fas fa-check"></i> Return for revision when changes are needed — the requester can resubmit.</li>
                            <li><i class="fas fa-check"></i> Reject when the request should not proceed; always leave a clear reason.</li>
                        </ul>
                    </section>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
