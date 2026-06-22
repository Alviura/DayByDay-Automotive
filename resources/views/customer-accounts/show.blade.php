<x-app-layout :title="$customerAccount->name">

    @push('styles')
        <x-module.page-index-styles />
        @include('customer-accounts.partials.page-styles')
        @include('returns.partials.page-styles')
    @endpush

    @php
        $payBadge = fn (string $status) => match ($status) {
            'paid' => 'ca-pay-paid',
            'partial' => 'ca-pay-partial',
            default => 'ca-pay-unpaid',
        };
    @endphp

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-bus"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $customerAccount->name }}</h1>
                        <span class="ca-badge {{ $customerAccount->is_active ? 'ca-badge-active' : 'ca-badge-inactive' }}">
                            {{ $customerAccount->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        @if ($customerAccount->contact_name)
                            <span><i class="fas fa-user text-[0.6rem] text-gray-400"></i> {{ $customerAccount->contact_name }}</span>
                        @endif
                        <span class="ca-terms-badge"><i class="fas fa-calendar-days text-[0.55rem]"></i> {{ $customerAccount->billing_terms }} billing</span>
                        @if ($customerAccount->phone)
                            <span><i class="fas fa-phone text-[0.6rem] text-gray-400"></i> {{ $customerAccount->phone }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('customer-accounts.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i> Back
                </a>
                @can('customer_invoices.manage')
                    <a href="{{ route('customer-invoices.create', ['account_id' => $customerAccount->id]) }}" class="mi-btn-orange">
                        <i class="fas fa-file-invoice text-xs"></i> Generate Invoice
                    </a>
                @endcan
                @can('customer_accounts.manage')
                    <a href="{{ route('customer-accounts.edit', $customerAccount) }}" class="mi-btn-ghost">
                        <i class="fas fa-pen text-xs"></i> Edit
                    </a>
                @endcan
            </div>
        </div>

        {{-- Status banner --}}
        @if ($unpaidSales->isNotEmpty())
            <div class="ca-show-banner ca-show-banner-warn">
                <div class="ca-show-banner-text">
                    <i class="fas fa-receipt mr-1"></i>
                    <strong>{{ $unpaidSales->count() }}</strong> uninvoiced sale{{ $unpaidSales->count() === 1 ? '' : 's' }}
                    totalling <strong>KES {{ number_format($uninvoicedTotal, 2) }}</strong> — ready for month-end billing.
                </div>
                @can('customer_invoices.manage')
                    <a href="{{ route('customer-invoices.create', ['account_id' => $customerAccount->id]) }}" class="mi-btn-orange">
                        <i class="fas fa-file-invoice text-xs"></i> Generate Invoice
                    </a>
                @endcan
            </div>
        @elseif ($outstanding <= 0 && $totalCreditSales > 0)
            <div class="ca-show-banner ca-show-banner-ok">
                <div class="ca-show-banner-text">
                    <i class="fas fa-circle-check mr-1"></i>
                    Account is fully settled — no outstanding balance.
                </div>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Outstanding</p>
                    <p class="mi-kpi-value orange">{{ number_format($outstanding, 2) }}</p>
                    <p class="ca-kpi-sub">
                        KES unpaid on credit sales
                        @if ($returnRefunds > 0)
                            · {{ number_format($returnRefunds, 2) }} refunded via returns
                        @endif
                    </p>
                    @if ($creditUsedPct !== null)
                        <div class="ca-credit-meter" title="{{ $creditUsedPct }}% of credit limit">
                            <div class="ca-credit-meter-fill {{ $creditUsedPct >= 80 ? 'warn' : '' }}" style="width: {{ $creditUsedPct }}%"></div>
                        </div>
                    @endif
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Uninvoiced</p>
                    <p class="mi-kpi-value">{{ $unpaidSales->count() }}</p>
                    <p class="ca-kpi-sub">KES {{ number_format($uninvoicedTotal, 0) }} awaiting invoice</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Credit Sales</p>
                    <p class="mi-kpi-value">{{ number_format($totalCreditSales) }}</p>
                    <p class="ca-kpi-sub">Completed on account</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-cart-shopping"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Credit Limit</p>
                    <p class="mi-kpi-value">{{ $customerAccount->credit_limit ? number_format($customerAccount->credit_limit, 0) : '—' }}</p>
                    <p class="ca-kpi-sub">{{ $customerAccount->credit_limit ? 'KES maximum exposure' : 'No limit set' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-gauge-high"></i></div>
            </div>
        </div>

        {{-- Main + sidebar --}}
        <div class="ca-show-grid">
            <div class="space-y-5">

                {{-- Account overview --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-circle-info text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Account overview</span>
                        </div>
                    </div>
                    <dl class="mi-detail-grid">
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-user"></i> Contact</dt>
                            <dd class="mi-detail-value">{{ $customerAccount->contact_name ?? '—' }}</dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-phone"></i> Phone</dt>
                            <dd class="mi-detail-value">
                                @if ($customerAccount->phone) {{ $customerAccount->phone }}
                                @else <span class="mi-detail-empty">Not provided</span> @endif
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-envelope"></i> Email</dt>
                            <dd class="mi-detail-value">
                                @if ($customerAccount->email)
                                    <a href="mailto:{{ $customerAccount->email }}" class="text-orange-600 hover:underline">{{ $customerAccount->email }}</a>
                                @else
                                    <span class="mi-detail-empty">Not provided</span>
                                @endif
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-calendar-days"></i> Billing</dt>
                            <dd class="mi-detail-value"><span class="ca-terms-badge">{{ $customerAccount->billing_terms }}</span></dd>
                        </div>
                        @if ($customerAccount->notes)
                            <div class="mi-detail-item mi-span-full">
                                <dt class="mi-detail-label"><i class="fas fa-note-sticky"></i> Notes</dt>
                                <dd class="mi-detail-value text-sm text-gray-600 whitespace-pre-line">{{ $customerAccount->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Uninvoiced sales --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Uninvoiced credit sales</p>
                            <p class="text-xs text-gray-400 mt-0.5">Sales ready to roll into the next invoice</p>
                        </div>
                        @if ($unpaidSales->isNotEmpty())
                            <span class="ca-uninvoiced">{{ $unpaidSales->count() }} pending</span>
                        @endif
                    </div>

                    @if ($unpaidSales->isNotEmpty())
                        <div class="mi-table-wrap">
                            <table class="mi-table">
                                <thead>
                                    <tr>
                                        <th>Receipt</th>
                                        <th>Date</th>
                                        <th>Vehicle</th>
                                        <th>Shop</th>
                                        <th>Lines</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unpaidSales as $sale)
                                        <tr class="ca-sale-row" onclick="window.location='{{ route('sales.show', $sale) }}'">
                                            <td><span class="ca-receipt">{{ $sale->receipt_number }}</span></td>
                                            <td class="text-sm text-gray-500">{{ $sale->sold_at?->format('d M Y') }}</td>
                                            <td>
                                                @if ($sale->vehicle_plate)
                                                    <span class="ca-plate">{{ $sale->vehicle_plate }}</span>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                            <td class="text-sm text-gray-600">{{ $sale->shop?->name ?? '—' }}</td>
                                            <td class="text-sm">{{ $sale->items_count }}</td>
                                            <td class="font-bold text-orange-700">{{ number_format($sale->total, 2) }}</td>
                                            <td>
                                                <a href="{{ route('sales.show', $sale) }}" class="mi-action view" onclick="event.stopPropagation()">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-amber-50/60">
                                        <td colspan="5" class="text-right text-sm font-semibold text-gray-600">Uninvoiced total</td>
                                        <td class="font-bold text-orange-700">{{ number_format($uninvoicedTotal, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="mi-show-empty">
                            <i class="fas fa-receipt"></i>
                            <p>No uninvoiced sales — all credit activity has been billed.</p>
                        </div>
                    @endif
                </div>

                {{-- Recent credit sales --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Recent credit sales</p>
                            <p class="text-xs text-gray-400 mt-0.5">Last 10 completed on-account transactions</p>
                        </div>
                    </div>

                    @if ($recentSales->isNotEmpty())
                        <div class="mi-table-wrap">
                            <table class="mi-table">
                                <thead>
                                    <tr>
                                        <th>Receipt</th>
                                        <th>Date</th>
                                        <th>Vehicle</th>
                                        <th>Payment</th>
                                        <th>Invoice</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentSales as $sale)
                                        <tr class="ca-sale-row" onclick="window.location='{{ route('sales.show', $sale) }}'">
                                            <td><span class="ca-receipt">{{ $sale->receipt_number }}</span></td>
                                            <td class="text-sm text-gray-500">{{ $sale->sold_at?->format('d M Y') }}</td>
                                            <td>
                                                @if ($sale->vehicle_plate)
                                                    <span class="ca-plate">{{ $sale->vehicle_plate }}</span>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                            <td><span class="ca-pay-badge {{ $payBadge($sale->payment_status) }}">{{ $sale->payment_status }}</span></td>
                                            <td>
                                                @if ($sale->customerInvoice)
                                                    <a href="{{ route('customer-invoices.show', $sale->customerInvoice) }}" class="ca-receipt text-orange-600 hover:underline" onclick="event.stopPropagation()">{{ $sale->customerInvoice->invoice_number }}</a>
                                                @else
                                                    <span class="text-gray-300 text-xs">—</span>
                                                @endif
                                            </td>
                                            <td class="font-semibold">{{ number_format($sale->total, 2) }}</td>
                                            <td>
                                                <a href="{{ route('sales.show', $sale) }}" class="mi-action view" onclick="event.stopPropagation()">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="mi-show-empty">
                            <i class="fas fa-cart-shopping"></i>
                            <p>No credit sales yet — issue the first order from Order Entry in Fleet mode.</p>
                        </div>
                    @endif
                </div>

                @if ($recentReturns->isNotEmpty())
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Customer returns</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Refunds on credit sales reduce outstanding
                                    @if ($returnRefunds > 0)
                                        · KES {{ number_format($returnRefunds, 2) }} completed
                                    @endif
                                </p>
                            </div>
                            @can('returns.view')
                                <a href="{{ route('customer-returns.index') }}" class="text-xs text-orange-600 hover:underline font-semibold">View all</a>
                            @endcan
                        </div>
                        <div class="mi-table-wrap">
                            <table class="mi-table">
                                <thead>
                                    <tr>
                                        <th>Return</th>
                                        <th>Sale</th>
                                        <th>Shop</th>
                                        <th>Refund</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentReturns as $return)
                                        <tr class="ca-sale-row" onclick="window.location='{{ route('customer-returns.show', $return) }}'">
                                            <td><span class="ca-receipt">{{ $return->return_number }}</span></td>
                                            <td class="font-mono text-sm">{{ $return->sale?->receipt_number ?? '—' }}</td>
                                            <td class="text-sm text-gray-500">{{ $return->shop?->name }}</td>
                                            <td class="font-semibold">
                                                @if ($return->status === 'completed')
                                                    {{ number_format($return->refund_amount, 2) }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td>@include('returns.partials.status-badge', ['return' => $return])</td>
                                            <td>
                                                <a href="{{ route('customer-returns.show', $return) }}" class="mi-action view" onclick="event.stopPropagation()">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Recent invoices --}}
                @if ($recentInvoices->isNotEmpty())
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Recent invoices</p>
                                <p class="text-xs text-gray-400 mt-0.5">Monthly statements for this account</p>
                            </div>
                            @can('customer_invoices.view')
                                <a href="{{ route('customer-invoices.index', ['account_id' => $customerAccount->id]) }}" class="text-xs text-orange-600 hover:underline font-semibold">View all</a>
                            @endcan
                        </div>
                        <div class="mi-table-wrap">
                            <table class="mi-table">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Period</th>
                                        <th>Issued</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentInvoices as $invoice)
                                        <tr class="ca-sale-row" onclick="window.location='{{ route('customer-invoices.show', $invoice) }}'">
                                            <td><span class="ca-receipt">{{ $invoice->invoice_number }}</span></td>
                                            <td class="text-sm text-gray-500">{{ $invoice->period_start->format('d M') }} – {{ $invoice->period_end->format('d M Y') }}</td>
                                            <td class="text-sm text-gray-500">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</td>
                                            <td><span class="{{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span></td>
                                            <td class="font-semibold">{{ number_format($invoice->total, 2) }}</td>
                                            <td>
                                                <a href="{{ route('customer-invoices.show', $invoice) }}" class="mi-action view" onclick="event.stopPropagation()">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <x-module.show-sidebar
                :model="$customerAccount"
                :edit-url="route('customer-accounts.edit', $customerAccount)"
                :index-url="route('customer-accounts.index')"
                edit-label="Edit Account"
                index-label="All Accounts"
                manage-permission="customer_accounts.manage"
            >
                <x-slot:footer>
                    <x-customer-account.show-sidebar-extra
                        :customer-account="$customerAccount"
                        :outstanding="$outstanding"
                        :uninvoiced-count="$unpaidSales->count()"
                    />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>
