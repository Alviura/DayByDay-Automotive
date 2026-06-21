<div class="mi-form-split">
    <div class="mi-form-main space-y-5">
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="qs-section-title"><i class="fas fa-circle-info"></i> Series Details</div>
            </div>
            <dl class="mi-detail-grid">
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-tag"></i> Display Name</dt>
                    <dd class="mi-detail-value font-semibold">{{ $series->displayName() }}</dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-hashtag"></i> Reference</dt>
                    <dd class="mi-detail-value"><span class="mi-cat-badge">{{ $series->series_number }}</span></dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-truck"></i> Supplier</dt>
                    <dd class="mi-detail-value">
                        @if ($series->supplier)
                            <a href="{{ route('suppliers.show', $series->supplier) }}" class="text-orange-600 hover:text-orange-700">{{ $series->supplier->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-globe"></i> Purchase Type</dt>
                    <dd class="mi-detail-value">
                        <span class="mi-cat-badge {{ $series->isImport() ? 'qs-type-import' : 'qs-type-local' }}">
                            {{ ucfirst($series->purchase_type ?? 'local') }}
                        </span>
                    </dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-coins"></i> Currency</dt>
                    <dd class="mi-detail-value">{{ $series->currency }}</dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-right-left"></i> Conversion (R)</dt>
                    <dd class="mi-detail-value">{{ number_format($series->exchange_rate, 4) }}</dd>
                </div>
                @if ($series->isImport())
                    <div class="mi-detail-item">
                        <dt class="mi-detail-label"><i class="fas fa-cube"></i> CBM Rate (R)</dt>
                        <dd class="mi-detail-value">{{ $series->cbm_rate ? number_format($series->cbm_rate, 2) : '—' }}</dd>
                    </div>
                @endif
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-list"></i> Line Items</dt>
                    <dd class="mi-detail-value">{{ $series->items->count() }}</dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-circle-check"></i> Approved</dt>
                    <dd class="mi-detail-value">{{ $series->approved_at?->format('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div class="mi-detail-item">
                    <dt class="mi-detail-label"><i class="fas fa-calendar"></i> Created</dt>
                    <dd class="mi-detail-value">{{ $series->created_at?->format('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div class="mi-detail-item mi-span-full">
                    <dt class="mi-detail-label"><i class="fas fa-note-sticky"></i> Notes</dt>
                    <dd class="mi-detail-value">{{ $series->notes ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        @if ($series->isCalculated())
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="qs-section-title"><i class="fas fa-chart-pie"></i> Financial Summary</div>
                </div>
                <dl class="mi-detail-grid">
                    <div class="mi-detail-item">
                        <dt class="mi-detail-label">Purchase Total</dt>
                        <dd class="mi-detail-value font-semibold">{{ number_format($series->total_purchase_price, 2) }} {{ $series->isImport() ? $series->currency : 'KES' }}</dd>
                    </div>
                    <div class="mi-detail-item">
                        <dt class="mi-detail-label">Transport</dt>
                        <dd class="mi-detail-value">{{ number_format($series->total_transport_cost, 2) }} KES</dd>
                    </div>
                    <div class="mi-detail-item">
                        <dt class="mi-detail-label">Actual Cost</dt>
                        <dd class="mi-detail-value text-orange-600 font-semibold">{{ number_format($series->total_actual_cost, 2) }} KES</dd>
                    </div>
                    <div class="mi-detail-item">
                        <dt class="mi-detail-label">Expected Sales</dt>
                        <dd class="mi-detail-value">{{ number_format($series->total_expected_sales, 2) }} KES</dd>
                    </div>
                    <div class="mi-detail-item">
                        <dt class="mi-detail-label">Expected Margin</dt>
                        <dd class="mi-detail-value text-green-700 font-semibold">{{ number_format($series->total_expected_margin, 2) }} KES</dd>
                    </div>
                    @if ($series->isImport() && $series->total_cbm)
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label">Total CBM</dt>
                            <dd class="mi-detail-value">{{ number_format($series->total_cbm, 2) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </div>

    <x-quotation-series.show-guide :series="$series" :workflow-steps="$workflowSteps" />
</div>
