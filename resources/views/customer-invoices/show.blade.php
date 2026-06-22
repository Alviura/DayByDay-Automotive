<x-app-layout :title="$customerInvoice->invoice_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('customer-invoices.partials.page-styles')
    @endpush

    @php
        $balanceDue = $customerInvoice->balanceDue();
        $isOverdue = $customerInvoice->isOverdue();
    @endphp

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold font-mono text-gray-900 leading-tight">{{ $customerInvoice->invoice_number }}</h1>
                        <span class="{{ $customerInvoice->statusBadgeClass() }}">{{ $customerInvoice->statusLabel() }}</span>
                        @if ($isOverdue)
                            <span class="ci-badge ci-badge-rose">Overdue</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        @if ($customerInvoice->account)
                            <a href="{{ route('customer-accounts.show', $customerInvoice->account) }}" class="text-orange-600 hover:underline font-semibold">
                                <i class="fas fa-bus text-[0.6rem]"></i> {{ $customerInvoice->account->name }}
                            </a>
                        @endif
                        <span>{{ $customerInvoice->period_start->format('d M Y') }} – {{ $customerInvoice->period_end->format('d M Y') }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="mi-btn-orange">
                    <i class="fas fa-print text-xs"></i> Print Statement
                </button>
                <a href="{{ route('customer-invoices.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i> Back
                </a>
            </div>
        </div>

        {{-- Status banner --}}
        @if ($isOverdue)
            <div class="ci-show-banner ci-show-banner-overdue no-print">
                <span><i class="fas fa-clock mr-1"></i> This invoice is <strong>overdue</strong> — due {{ $customerInvoice->due_at->format('d M Y') }}. Balance: <strong>KES {{ number_format($balanceDue, 2) }}</strong></span>
            </div>
        @elseif ($customerInvoice->status === 'paid')
            <div class="ci-show-banner ci-show-banner-paid no-print">
                <span><i class="fas fa-circle-check mr-1"></i> Invoice fully paid — KES {{ number_format($customerInvoice->total, 2) }} collected.</span>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="mi-kpi-row no-print">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Invoice Total</p>
                    <p class="mi-kpi-value">{{ number_format($customerInvoice->total, 2) }}</p>
                    <p class="ci-kpi-sub">{{ $customerInvoice->sales->count() }} credit sale{{ $customerInvoice->sales->count() === 1 ? '' : 's' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-file-invoice"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Amount Paid</p>
                    <p class="mi-kpi-value">{{ number_format($customerInvoice->amount_paid, 2) }}</p>
                    <p class="ci-kpi-sub">{{ $customerInvoice->payments->count() }} payment{{ $customerInvoice->payments->count() === 1 ? '' : 's' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Balance Due</p>
                    <p class="mi-kpi-value orange">{{ number_format($balanceDue, 2) }}</p>
                    <p class="ci-kpi-sub">KES remaining</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Due Date</p>
                    <p class="mi-kpi-value text-status">{{ $customerInvoice->due_at?->format('d M') ?? '—' }}</p>
                    <p class="ci-kpi-sub">Issued {{ $customerInvoice->issued_at?->format('d M Y') ?? '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
        </div>

        {{-- Document + sidebar --}}
        <div class="ci-show-grid">
            <div>
                @include('customer-invoices.partials.document', ['invoice' => $customerInvoice])
            </div>

            <aside class="mi-guide no-print">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Invoice Info</h2>
                        <p class="mi-guide-subtitle">Details &amp; actions</p>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <section class="mi-guide-section mi-guide-section-first">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-hashtag"></i> Invoice</span>
                                <span class="mi-show-meta-value mono">{{ $customerInvoice->invoice_number }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-bus"></i> Account</span>
                                <span class="mi-show-meta-value">{{ $customerInvoice->account?->name ?? '—' }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-calendar-range"></i> Period</span>
                                <span class="mi-show-meta-value">{{ $customerInvoice->period_start->format('d M') }} – {{ $customerInvoice->period_end->format('d M Y') }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-calendar-plus"></i> Created</span>
                                <span class="mi-show-meta-value">{{ $customerInvoice->created_at->format('d M Y') }}</span>
                                <span class="mi-show-meta-sub">{{ $customerInvoice->created_at->format('H:i') }}</span>
                            </li>
                            @if ($customerInvoice->creator)
                                <li>
                                    <span class="mi-show-meta-label"><i class="fas fa-user"></i> Prepared by</span>
                                    <span class="mi-show-meta-value">{{ $customerInvoice->creator->name }}</span>
                                </li>
                            @endif
                        </ul>
                    </section>

                    <section class="mi-guide-section">
                        <h3 class="mi-guide-section-title"><i class="fas fa-bolt"></i> Quick actions</h3>
                        <div class="mi-show-actions">
                            <button type="button" onclick="window.print()" class="mi-btn-orange w-full justify-center">
                                <i class="fas fa-print text-xs"></i> Print Statement
                            </button>
                            @if ($customerInvoice->account)
                                <a href="{{ route('customer-accounts.show', $customerInvoice->account) }}" class="mi-btn-ghost w-full justify-center">
                                    <i class="fas fa-bus text-xs"></i> View Account
                                </a>
                            @endif
                            <a href="{{ route('customer-invoices.index') }}" class="mi-btn-ghost w-full justify-center">
                                <i class="fas fa-list text-xs"></i> All Invoices
                            </a>
                        </div>
                    </section>

                    @if ($customerInvoice->status !== 'paid')
                        @can('customer_invoices.manage')
                            <section class="mi-guide-section">
                                <h3 class="mi-guide-section-title"><i class="fas fa-wallet"></i> Record payment</h3>
                                <div class="ci-pay-panel !p-0" x-data="{ payments: [{ method: 'cash', amount: {{ $balanceDue }}, reference: '' }] }">
                                    <form method="POST" action="{{ route('customer-invoices.record-payment', $customerInvoice) }}">
                                        @csrf
                                        <template x-for="(payment, pi) in payments" :key="pi">
                                            <div class="ci-pay-row">
                                                <div>
                                                    <label class="mi-field-label">Method</label>
                                                    <select :name="`payments[${pi}][method]`" class="mi-select w-full" x-model="payment.method">
                                                        @foreach ($paymentMethods as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="mi-field-label">Amount (KES)</label>
                                                    <input type="number" step="0.01" min="0.01" class="mi-input w-full" :name="`payments[${pi}][amount]`" x-model.number="payment.amount">
                                                </div>
                                                <div class="col-span-2" x-show="payment.method !== 'cash'" style="grid-column: 1 / -1;">
                                                    <label class="mi-field-label">Reference</label>
                                                    <input type="text" class="mi-input w-full" :name="`payments[${pi}][reference]`" x-model="payment.reference" placeholder="M-Pesa code, cheque #…">
                                                </div>
                                            </div>
                                        </template>
                                        <button type="submit" class="mi-btn-orange w-full justify-center">
                                            <i class="fas fa-check text-xs"></i> Record Payment
                                        </button>
                                    </form>
                                </div>
                            </section>
                        @endcan
                    @endif

                    @if ($customerInvoice->payments->isNotEmpty())
                        <section class="mi-guide-section">
                            <h3 class="mi-guide-section-title"><i class="fas fa-clock-rotate-left"></i> Payment history</h3>
                            <ul class="mi-guide-list !gap-2">
                                @foreach ($customerInvoice->payments as $payment)
                                    <li>
                                        <strong>{{ $payment->methodLabel() }}</strong>
                                        <span>KES {{ number_format($payment->amount, 2) }} · {{ $payment->paid_at->format('d M Y') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
