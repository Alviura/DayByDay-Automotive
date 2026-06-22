<x-app-layout :title="$sale->receipt_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('sales.partials.page-styles')
        @include('returns.partials.page-styles')
    @endpush

    @php
        $paymentIcon = fn (string $method) => match ($method) {
            'cash' => ['fa-money-bill-wave', 'sl-pay-cash'],
            'mpesa' => ['fa-mobile-screen', 'sl-pay-mpesa'],
            'card' => ['fa-credit-card', 'sl-pay-card'],
            'bank_transfer' => ['fa-building-columns', 'sl-pay-bank'],
            default => ['fa-wallet', 'sl-pay-other'],
        };
        $cashier = $sale->completedBy ?? $sale->cashier;
        $soldAt = $sale->sold_at ?? $sale->created_at;
    @endphp

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-receipt"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight font-mono">{{ $sale->receipt_number }}</h1>
                        @include('sales.partials.status-badge', ['sale' => $sale])
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        <span class="mi-dest"><i class="fas fa-store"></i> {{ $sale->shop?->name }}</span>
                        @if ($sale->orderedBy)
                            <span>· Ordered by {{ $sale->orderedBy->name }}</span>
                        @endif
                        @if ($sale->completedBy)
                            <span>· Checked out by {{ $sale->completedBy->name }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sales.index', ['shop_id' => $sale->shop_id]) }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i> Back
                </a>
                @if ($sale->status === 'completed')
                    <a href="{{ route('receipts.show', $sale) }}" class="mi-btn-orange" target="_blank">
                        <i class="fas fa-print text-xs"></i> Receipt
                    </a>
                @endif
                @if ($sale->canComplete())
                    @can('sales.create')
                        <a href="{{ route('sales.desk.checkout', $sale) }}" class="mi-btn-orange">
                            <i class="fas fa-cash-register text-xs"></i> Checkout
                        </a>
                    @endcan
                @endif
                @if ($sale->canReverse())
                    @can('sales.reverse')
                        <form action="{{ route('sales.reverse', $sale) }}" method="POST" class="inline" data-confirm="Reverse this sale and restore stock?" data-confirm-variant="danger">
                            @csrf
                            <button type="submit" class="mi-btn-ghost !text-red-600 !border-red-100 hover:!bg-red-50">
                                <i class="fas fa-rotate-left text-xs"></i> Reverse
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Status banners --}}
        @if ($sale->isHeld())
            <div class="sl-show-banner sl-show-banner-held">
                <div class="sl-show-banner-text">
                    <i class="fas fa-hourglass-half text-amber-500 mr-1"></i>
                    This order is <strong>waiting at the cash desk</strong>.
                    @if ($sale->submitted_at)
                        Sent {{ $sale->submitted_at->diffForHumans() }}.
                    @endif
                </div>
                @can('sales.create')
                    <a href="{{ route('sales.desk.checkout', $sale) }}" class="mi-btn-orange">
                        <i class="fas fa-cash-register text-xs"></i> Checkout now
                    </a>
                @endcan
            </div>
        @elseif ($sale->status === 'reversed')
            <div class="sl-show-banner sl-show-banner-reversed">
                <div class="sl-show-banner-text">
                    <i class="fas fa-rotate-left text-rose-500 mr-1"></i>
                    This sale was <strong>reversed</strong>
                    @if ($sale->reversed_at)
                        on {{ $sale->reversed_at->format('d M Y H:i') }}
                    @endif
                    @if ($sale->reverser)
                        by {{ $sale->reverser->name }}
                    @endif
                    .
                </div>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="sl-kpi-grid">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Subtotal</p>
                    <p class="mi-kpi-value">{{ number_format($sale->subtotal, 2) }}</p>
                    <p class="sl-show-kpi-sub">KES before tax</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Tax</p>
                    <p class="mi-kpi-value">{{ number_format($sale->tax_total, 2) }}</p>
                    <p class="sl-show-kpi-sub">{{ $sale->items->count() }} line{{ $sale->items->count() === 1 ? '' : 's' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-tag"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Total</p>
                    <p class="mi-kpi-value orange">{{ number_format($sale->total, 2) }}</p>
                    <p class="sl-show-kpi-sub">KES @if($sale->tax_total > 0) incl. {{ number_format($sale->tax_total, 2) }} tax @else no tax @endif</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">{{ $sale->status === 'held' ? 'Est. due' : 'Paid' }}</p>
                    <p class="mi-kpi-value">{{ number_format($sale->status === 'held' ? $sale->total : $sale->amount_paid, 2) }}</p>
                    <p class="sl-show-kpi-sub">
                        @if ($sale->change_due > 0)
                            Change {{ number_format($sale->change_due, 2) }}
                        @else
                            {{ $sale->paymentStatusLabel() }}
                        @endif
                    </p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $sale->status === 'held' ? 'hourglass-half' : 'circle-check' }}"></i></div>
            </div>
        </div>

        <div class="sl-show-grid">
            {{-- Line items --}}
            <div class="mi-card">
                <div class="mi-card-head">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Order lines</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $sale->items->count() }} product{{ $sale->items->count() === 1 ? '' : 's' }}</p>
                    </div>
                </div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit price</th>
                                <th>Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>
                                        <div class="sl-line-cell">
                                            <div class="sl-line-icon"><i class="fas fa-box"></i></div>
                                            <div>
                                                <p class="sl-line-name">{{ $item->product->name }}</p>
                                                <p class="sl-line-sku">{{ $item->product->part_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-semibold text-gray-700">
                                        {{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }}
                                        @if ($item->product->unit)
                                            <span class="text-xs text-gray-400 font-normal">{{ $item->product->unit->abbreviation ?? $item->product->unit->name }}</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="font-bold text-orange-700">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50/80">
                                <td colspan="4" class="text-right text-sm font-semibold text-gray-500 pr-4">Grand total</td>
                                <td class="font-bold text-lg text-orange-700">{{ number_format($sale->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if ($sale->status === 'completed' && $sale->returnRecords->isNotEmpty())
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Returns</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $sale->returnRecords->count() }} return{{ $sale->returnRecords->count() === 1 ? '' : 's' }} against this sale</p>
                        </div>
                        @can('returns.create')
                            <a href="{{ route('customer-returns.create', ['sale_id' => $sale->id]) }}" class="text-xs text-orange-600 hover:underline font-semibold">
                                <i class="fas fa-plus text-[0.6rem]"></i> New return
                            </a>
                        @endcan
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Return #</th>
                                    <th>Lines</th>
                                    <th>Refund</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale->returnRecords as $return)
                                    <tr class="rt-index-row" onclick="window.location='{{ route('customer-returns.show', $return) }}'">
                                        <td><span class="rt-ref">{{ $return->return_number }}</span></td>
                                        <td>{{ $return->items_count }}</td>
                                        <td class="font-semibold">
                                            @if ($return->status === 'completed')
                                                {{ number_format($return->refund_amount, 2) }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td>@include('returns.partials.status-badge', ['return' => $return])</td>
                                        <td class="text-sm text-gray-500">{{ $return->created_at->format('d M Y') }}</td>
                                        <td><i class="fas fa-chevron-right text-xs text-gray-300"></i></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Sidebar --}}
            <div class="sl-show-side">
                {{-- Sale details --}}
                <div class="mi-card sl-detail-card">
                    <p class="sl-detail-title"><i class="fas fa-circle-info"></i> Sale details</p>
                    <dl class="sl-detail-list">
                        <div>
                            <dt>Status</dt>
                            <dd>@include('sales.partials.status-badge', ['sale' => $sale])</dd>
                        </div>
                        <div>
                            <dt>Sale type</dt>
                            <dd>{{ $sale->saleTypeLabel() }}</dd>
                        </div>
                        @if ($sale->customerAccount)
                            <div>
                                <dt>Fleet account</dt>
                                <dd><a href="{{ route('customer-accounts.show', $sale->customerAccount) }}" class="text-orange-600 hover:underline">{{ $sale->customerAccount->name }}</a></dd>
                            </div>
                        @endif
                        @if ($sale->vehicle_plate)
                            <div>
                                <dt>Vehicle</dt>
                                <dd>{{ $sale->vehicle_plate }}</dd>
                            </div>
                        @endif
                        @if ($sale->customerInvoice)
                            <div>
                                <dt>Invoice</dt>
                                <dd><a href="{{ route('customer-invoices.show', $sale->customerInvoice) }}" class="text-orange-600 hover:underline font-mono text-xs">{{ $sale->customerInvoice->invoice_number }}</a></dd>
                            </div>
                        @endif
                        <div>
                            <dt>Shop</dt>
                            <dd>{{ $sale->shop?->name ?? '—' }}</dd>
                        </div>
                        @if ($sale->customer_name || $sale->customer_phone)
                            <div>
                                <dt>Customer</dt>
                                <dd>
                                    {{ $sale->customer_name ?: 'Walk-in' }}
                                    @if ($sale->customer_phone)<br><span class="text-gray-400 font-normal">{{ $sale->customer_phone }}</span>@endif
                                </dd>
                            </div>
                        @endif
                        @if ($sale->orderedBy)
                            <div>
                                <dt>Ordered by</dt>
                                <dd>{{ $sale->orderedBy->name }}</dd>
                            </div>
                        @endif
                        @if ($cashier)
                            <div>
                                <dt>{{ $sale->status === 'held' ? 'Created by' : 'Cashier' }}</dt>
                                <dd>{{ $cashier->name }}</dd>
                            </div>
                        @endif
                        @if ($sale->submitted_at)
                            <div>
                                <dt>Sent to desk</dt>
                                <dd>{{ $sale->submitted_at->format('d M Y H:i') }}</dd>
                            </div>
                        @endif
                        @if ($sale->sold_at)
                            <div>
                                <dt>Completed</dt>
                                <dd>{{ $sale->sold_at->format('d M Y H:i') }}</dd>
                            </div>
                        @else
                            <div>
                                <dt>Created</dt>
                                <dd>{{ $soldAt->format('d M Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Notes --}}
                @if ($sale->notes)
                    <div class="mi-card sl-detail-card">
                        <p class="sl-detail-title"><i class="fas fa-sticky-note"></i> Notes</p>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $sale->notes }}</p>
                    </div>
                @endif

                {{-- Payments --}}
                @if ($sale->payments->isNotEmpty())
                    <div class="mi-card sl-detail-card">
                        <p class="sl-detail-title"><i class="fas fa-wallet"></i> Payments</p>
                        @foreach ($sale->payments as $payment)
                            @php [$icon, $iconClass] = $paymentIcon($payment->method); @endphp
                            <div class="sl-pay-pill">
                                <div>
                                    <div class="sl-pay-method">
                                        <span class="sl-pay-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></span>
                                        {{ $payment->methodLabel() }}
                                    </div>
                                    @if ($payment->reference)
                                        <p class="sl-pay-ref">Ref: {{ $payment->reference }}</p>
                                    @endif
                                    @if ($payment->paid_at)
                                        <p class="sl-pay-ref">{{ $payment->paid_at->format('d M Y H:i') }}</p>
                                    @endif
                                </div>
                                <span class="sl-pay-amount">{{ number_format($payment->amount, 2) }}</span>
                            </div>
                        @endforeach
                        @if ($sale->change_due > 0)
                            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm">
                                <span class="text-gray-500">Change given</span>
                                <span class="font-bold text-green-600">{{ number_format($sale->change_due, 2) }}</span>
                            </div>
                        @endif
                    </div>
                @elseif ($sale->isHeld())
                    <div class="mi-card sl-detail-card">
                        <p class="sl-detail-title"><i class="fas fa-wallet"></i> Payment</p>
                        <p class="text-sm text-gray-400">Payment will be recorded when checkout is completed at the cash desk.</p>
                    </div>
                @endif

                {{-- Quick actions --}}
                <div class="mi-card sl-detail-card">
                    <p class="sl-detail-title"><i class="fas fa-bolt"></i> Actions</p>
                    <div class="sl-show-actions">
                        @if ($sale->status === 'completed')
                            <a href="{{ route('receipts.show', $sale) }}" class="mi-btn-orange" target="_blank">
                                <i class="fas fa-print text-xs"></i> Print receipt
                            </a>
                            @can('returns.create')
                                <a href="{{ route('customer-returns.create', ['sale_id' => $sale->id]) }}" class="mi-btn-ghost">
                                    <i class="fas fa-rotate-left text-xs"></i> Create return
                                </a>
                            @endcan
                        @endif
                        @if ($sale->canComplete())
                            @can('sales.create')
                                <a href="{{ route('sales.desk.checkout', $sale) }}" class="mi-btn-orange">
                                    <i class="fas fa-cash-register text-xs"></i> Checkout at desk
                                </a>
                            @endcan
                        @endif
                        <a href="{{ route('sales.desk', ['shop_id' => $sale->shop_id]) }}" class="mi-btn-ghost">
                            <i class="fas fa-list-ol text-xs"></i> Cash desk queue
                        </a>
                        <a href="{{ route('sales.index', ['shop_id' => $sale->shop_id]) }}" class="mi-btn-ghost">
                            <i class="fas fa-receipt text-xs"></i> Sales history
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
