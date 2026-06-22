@props(['invoice'])

@php
    $balanceDue = $invoice->balanceDue();
    $isOverdue = $balanceDue > 0 && $invoice->due_at && $invoice->due_at->isPast();
@endphp

<div class="ci-doc" id="invoice-print">
    <div class="ci-doc-accent"></div>
    <div class="ci-doc-body">

        {{-- Header --}}
        <div class="ci-doc-header">
            <div class="ci-doc-brand">
                <div class="ci-doc-logo"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <p class="ci-doc-company">{{ config('app.name') }}</p>
                    <p class="ci-doc-tagline">Fleet &amp; Credit Statement</p>
                </div>
            </div>
            <div class="ci-doc-title-block">
                <p class="ci-doc-type">Tax Invoice / Statement</p>
                <p class="ci-doc-number">{{ $invoice->invoice_number }}</p>
                <div class="ci-doc-status">
                    <span class="{{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span>
                </div>
            </div>
        </div>

        {{-- Bill to + dates --}}
        <div class="ci-doc-meta">
            <div>
                <p class="ci-doc-block-label">Bill to</p>
                <p class="ci-doc-bill-name">{{ $invoice->account?->name }}</p>
                <div class="ci-doc-bill-detail">
                    @if ($invoice->account?->contact_name)
                        <p>{{ $invoice->account->contact_name }}</p>
                    @endif
                    @if ($invoice->account?->phone)
                        <p><i class="fas fa-phone text-[0.55rem]"></i> {{ $invoice->account->phone }}</p>
                    @endif
                    @if ($invoice->account?->email)
                        <p><i class="fas fa-envelope text-[0.55rem]"></i> {{ $invoice->account->email }}</p>
                    @endif
                </div>
            </div>
            <div>
                <p class="ci-doc-block-label">Invoice details</p>
                <div class="ci-doc-dates">
                    <div class="ci-doc-date-row">
                        <span class="ci-doc-date-label">Issued</span>
                        <span class="ci-doc-date-value">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="ci-doc-date-row">
                        <span class="ci-doc-date-label">Due date</span>
                        <span class="ci-doc-date-value {{ $isOverdue ? 'overdue' : '' }}">
                            {{ $invoice->due_at?->format('d M Y') ?? '—' }}
                            @if ($isOverdue) (overdue) @endif
                        </span>
                    </div>
                    <div class="ci-doc-date-row">
                        <span class="ci-doc-date-label">Billing terms</span>
                        <span class="ci-doc-date-value capitalize">{{ $invoice->account?->billing_terms ?? 'monthly' }}</span>
                    </div>
                    @if ($invoice->creator)
                        <div class="ci-doc-date-row">
                            <span class="ci-doc-date-label">Prepared by</span>
                            <span class="ci-doc-date-value">{{ $invoice->creator->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="ci-doc-period">
            <i class="fas fa-calendar-range"></i>
            Billing period: {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}
        </div>

        {{-- Line items --}}
        <table class="ci-doc-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Receipt</th>
                    <th>Vehicle</th>
                    <th>Shop</th>
                    <th>Lines</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->sales as $sale)
                    <tr>
                        <td>{{ $sale->sold_at?->format('d M Y') }}</td>
                        <td><span class="ci-doc-receipt">{{ $sale->receipt_number }}</span></td>
                        <td>
                            @if ($sale->vehicle_plate)
                                <span class="ci-plate">{{ $sale->vehicle_plate }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-gray-500">{{ $sale->shop?->name ?? '—' }}</td>
                        <td>{{ $sale->items->count() }}</td>
                        <td>{{ number_format($sale->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals + notes --}}
        <div class="ci-doc-footer-grid">
            <div class="ci-doc-notes">
                @if ($invoice->notes)
                    <p class="ci-doc-block-label">Notes</p>
                    <p>{{ $invoice->notes }}</p>
                @else
                    <p class="ci-doc-block-label">Payment instructions</p>
                    <p>Please remit the full amount by the due date. Reference this invoice number when making payment. For queries, contact us using the details above.</p>
                @endif
            </div>
            <div class="ci-doc-totals">
                @if ($invoice->subtotal != $invoice->total)
                    <div class="ci-doc-total-row">
                        <span>Subtotal</span>
                        <span class="ci-doc-total-val">{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if ($invoice->tax_total > 0)
                        <div class="ci-doc-total-row">
                            <span>Tax</span>
                            <span class="ci-doc-total-val">{{ number_format($invoice->tax_total, 2) }}</span>
                        </div>
                    @endif
                @endif
                <div class="ci-doc-total-row">
                    <span>{{ $invoice->sales->count() }} credit sale{{ $invoice->sales->count() === 1 ? '' : 's' }}</span>
                    <span class="ci-doc-total-val">{{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="ci-doc-total-row grand">
                    <span>Total due</span>
                    <span class="ci-doc-total-val">{{ number_format($invoice->total, 2) }}</span>
                </div>
                @if ($invoice->amount_paid > 0)
                    <div class="ci-doc-total-row paid">
                        <span>Amount paid</span>
                        <span class="ci-doc-total-val">− {{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="ci-doc-total-row balance">
                        <span>Balance due</span>
                        <span class="ci-doc-total-val">{{ number_format($balanceDue, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payments received --}}
        @if ($invoice->payments->isNotEmpty())
            <div class="ci-doc-payments">
                <p class="ci-doc-payments-title"><i class="fas fa-circle-check"></i> Payments received</p>
                @foreach ($invoice->payments as $payment)
                    <div class="ci-doc-payment-item">
                        <span>
                            <strong>{{ $payment->methodLabel() }}</strong>
                            @if ($payment->reference)
                                <span class="text-gray-500"> · {{ $payment->reference }}</span>
                            @endif
                            <span class="text-gray-400 text-xs"> — {{ $payment->paid_at->format('d M Y') }}</span>
                        </span>
                        <strong>KES {{ number_format($payment->amount, 2) }}</strong>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="ci-doc-closing">
            Thank you for your business. This statement covers credit parts issued during the billing period listed above.
        </p>
    </div>
</div>
