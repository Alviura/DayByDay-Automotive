<x-app-layout :title="'VAT — '.$remittance->periodLabel()">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $remittance->periodLabel() }}</h1>
                    <p class="mt-0.5 text-sm text-gray-500">VAT remittance period</p>
                </div>
            </div>
            <a href="{{ route('tax-remittances.index', ['year' => $remittance->period_year]) }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'vat'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">VAT Collected</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($remittance->tax_collected, 2) }}</p>
                    <p class="fin-kpi-sub">From GL VAT payable account</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-percent"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Amount Remitted</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($remittance->amount_remitted, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-paper-plane"></i></div>
            </div>
            <div class="mi-kpi {{ $remittance->balanceDue() > 0 ? 'mi-kpi-amber' : 'mi-kpi-green' }}">
                <div>
                    <p class="mi-kpi-label">Balance Due</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($remittance->balanceDue(), 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-scale-balanced"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Status</p>
                    <p class="mi-kpi-value text-base">{{ $remittance->statusLabel() }}</p>
                    <p class="fin-kpi-sub">Due {{ $remittance->due_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-flag"></i></div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="fin-doc-card p-5">
                <h2 class="font-semibold text-gray-900 mb-4">Period Details</h2>
                <dl class="fin-dl">
                    <div><dt>Filed</dt><dd>{{ $remittance->filed_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Paid</dt><dd>{{ $remittance->paid_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Created by</dt><dd>{{ $remittance->creator?->name ?? '—' }}</dd></div>
                    @if ($remittance->notes)
                        <div><dt>Notes</dt><dd>{{ $remittance->notes }}</dd></div>
                    @endif
                </dl>
            </div>

            @can('finance.manage')
                @if ($remittance->status !== 'paid')
                    <div class="fin-doc-card p-5 space-y-4">
                        <h2 class="font-semibold text-gray-900">Actions</h2>

                        @if ($remittance->status === 'open')
                            <form method="POST" action="{{ route('tax-remittances.file', $remittance) }}">
                                @csrf
                                <button type="submit" class="mi-btn-orange w-full">
                                    <i class="fas fa-file-export text-xs"></i> Mark as Filed
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Confirms the VAT return has been submitted to KRA.</p>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('tax-remittances.pay', $remittance) }}" class="space-y-3 border-t border-gray-100 pt-4">
                            @csrf
                            <div>
                                <label class="mi-field-label">Payment amount (KES)</label>
                                <input type="number" name="amount" step="0.01" min="0.01"
                                    value="{{ number_format($remittance->balanceDue(), 2, '.', '') }}"
                                    class="mi-input w-full" required>
                            </div>
                            <button type="submit" class="mi-btn-orange w-full">
                                <i class="fas fa-money-bill-transfer text-xs"></i> Record Remittance
                            </button>
                            <p class="text-xs text-gray-500">Posts Dr VAT Payable / Cr Bank and marks period paid.</p>
                        </form>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
