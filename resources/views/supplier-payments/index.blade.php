<x-app-layout title="Supplier Payments">

    @push('styles')
        <x-module.page-index-styles />
        @include('supplier-payments.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','supplier_id','method','sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Supplier Payments</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Settle accounts payable against goods receipts and purchase orders.</p>
                </div>
            </div>
            @can('supplier_payments.manage')
                <a href="{{ route('supplier-payments.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> Record Payment
                </a>
            @endcan
        </div>

        @include('supplier-payments.partials.nav-tabs', ['active' => 'index'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">AP Outstanding</p>
                    <p class="mi-kpi-value sp-amt">{{ number_format($stats['outstanding'], 2) }}</p>
                    <p class="sp-kpi-sub">KES payable to suppliers</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-scale-unbalanced"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Total Paid</p>
                    <p class="mi-kpi-value sp-amt">{{ number_format($stats['posted_total'], 2) }}</p>
                    <p class="sp-kpi-sub">{{ number_format($stats['total']) }} posted payments</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">This Month</p>
                    <p class="mi-kpi-value sp-amt orange">{{ number_format($stats['this_month'], 2) }}</p>
                    <p class="sp-kpi-sub">KES paid out</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Suppliers Paid</p>
                    <p class="mi-kpi-value">{{ number_format($stats['suppliers_paid']) }}</p>
                    <p class="sp-kpi-sub">{{ number_format($stats['voided']) }} voided records</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>

        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Quick filter</p>
            <div class="sp-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $statusFilter === $step['key'];
                        $params = array_merge(request()->except('page', 'status'), ['status' => $step['key']]);
                    @endphp
                    <a href="{{ route('supplier-payments.index', $params) }}" class="sp-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="sp-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="sp-pipe-count">{{ number_format($step['count']) }}</span>
                        <span class="sp-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mi-filter-bar no-print">
            <button type="button" class="mi-filter-toggle" @click="filtersOpen = !filtersOpen">
                <i class="fas fa-sliders"></i> Filters
            </button>
            <form method="GET" class="mi-filter-form" x-show="filtersOpen" x-collapse>
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <div class="mi-filter-grid">
                    <div>
                        <label class="mi-field-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input w-full" placeholder="Payment #, supplier, reference…">
                    </div>
                    <div>
                        <label class="mi-field-label">Supplier</label>
                        <select name="supplier_id" class="mi-select w-full">
                            <option value="">All suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label">Method</label>
                        <select name="method" class="mi-select w-full">
                            <option value="">All methods</option>
                            @foreach (\App\Models\Payment::methods() as $value => $label)
                                <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label">Sort</label>
                        <select name="sort" class="mi-select w-full">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="amount" @selected(request('sort') === 'amount')>Highest amount</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">Apply</button>
                    <a href="{{ route('supplier-payments.index', ['status' => $statusFilter]) }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="sp-doc-card">
            @if ($payments->isEmpty())
                <div class="mi-empty py-16 text-center">
                    <div class="mi-empty-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                    <p class="font-semibold text-gray-700">No supplier payments found</p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if ($statusFilter === 'voided')
                            No voided payments match your filters.
                        @else
                            Record a payment against a posted goods receipt.
                        @endif
                    </p>
                    @can('supplier_payments.manage')
                        <a href="{{ route('supplier-payments.create') }}" class="mi-btn-orange mt-4 inline-flex">Record Payment</a>
                    @endcan
                </div>
            @else
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Supplier</th>
                            <th>Allocation</th>
                            <th>Method</th>
                            <th class="text-right">Amount</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr class="sp-index-row" onclick="window.location='{{ route('supplier-payments.show', $payment) }}'">
                                <td>
                                    <span class="sp-mono text-sm text-gray-800">{{ $payment->payment_number }}</span>
                                    @if ($payment->supplier_invoice_number)
                                        <p class="text-xs text-gray-500 mt-0.5">Inv {{ $payment->supplier_invoice_number }}</p>
                                    @endif
                                </td>
                                <td>
                                    <div class="sp-supplier-cell">
                                        <div class="sp-supplier-icon"><i class="fas fa-truck"></i></div>
                                        <span class="font-semibold text-sm text-gray-900 truncate">{{ $payment->supplier?->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($payment->goodsReceiptNote)
                                        <span class="sp-alloc-chip" onclick="event.stopPropagation()">
                                            <i class="fas fa-truck-ramp-box"></i>
                                            <a href="{{ route('goods-receipts.show', $payment->goodsReceiptNote) }}" class="text-orange-600 hover:underline">{{ $payment->goodsReceiptNote->grn_number }}</a>
                                        </span>
                                    @elseif ($payment->purchaseOrder)
                                        <span class="sp-alloc-chip" onclick="event.stopPropagation()">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                            <a href="{{ route('purchase-orders.show', $payment->purchaseOrder) }}" class="text-orange-600 hover:underline">{{ $payment->purchaseOrder->po_number }}</a>
                                        </span>
                                    @else
                                        <span class="sp-alloc-chip muted"><i class="fas fa-building"></i> On account</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="sp-method-pill {{ $payment->methodPillClass() }}">
                                        <i class="fas {{ $payment->methodIcon() }}"></i>
                                        {{ $payment->methodLabel() }}
                                    </span>
                                </td>
                                <td class="text-right sp-amt">{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-sm text-gray-500 whitespace-nowrap">{{ $payment->paid_at?->format('d M Y') }}</td>
                                <td><span class="{{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mi-table-footer">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
