<x-app-layout title="Fleet Invoices">

    @push('styles')
        <x-module.page-index-styles />
        @include('customer-invoices.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','account_id','status','sort']) ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Fleet Invoices</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Monthly statements for credit and PSV fleet accounts.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('customer_accounts.view')
                    <a href="{{ route('customer-accounts.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-bus text-xs"></i> Fleet Accounts
                    </a>
                @endcan
                @can('customer_invoices.manage')
                    <a href="{{ route('customer-invoices.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i> Generate Invoice
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Invoices</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="ci-kpi-sub">{{ $stats['paid'] }} paid · {{ $stats['sent'] + $stats['partially_paid'] }} open</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-file-invoice"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Outstanding</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['outstanding'], 0) }}</p>
                    <p class="ci-kpi-sub">KES balance due on open invoices</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Overdue</p>
                    <p class="mi-kpi-value">{{ $stats['overdue'] }}</p>
                    <p class="ci-kpi-sub">Past due date with balance</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Collected This Month</p>
                    <p class="mi-kpi-value">{{ number_format($stats['paid_this_month'], 0) }}</p>
                    <p class="ci-kpi-sub">KES from paid invoices</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>

        {{-- Pipeline --}}
        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Quick filter</p>
            <div class="ci-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $step['key'] === ''
                            ? ! request('status')
                            : request('status') === $step['key'];
                        $params = $step['key'] === ''
                            ? request()->except('page', 'status')
                            : array_merge(request()->except('page'), ['status' => $step['key']]);
                    @endphp
                    <a href="{{ route('customer-invoices.index', $params) }}"
                       class="ci-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="ci-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="ci-pipe-count">{{ $step['count'] }}</span>
                        <span class="ci-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Filters --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if (request()->hasAny(['search','account_id','status','sort']))
                        <span class="mi-cat-badge">Active</span>
                    @endif
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition>
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Invoice # or account name…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-bus"></i> Account</label>
                        <select name="account_id" class="mi-select">
                            <option value="">All accounts</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-traffic-light"></i> Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All statuses</option>
                            @foreach (['sent' => 'Sent', 'partially_paid' => 'Partially paid', 'paid' => 'Paid'] as $val => $label)
                                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="balance" @selected(request('sort') === 'balance')>Highest balance</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('customer-invoices.index') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $invoices->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $invoices->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $invoices->total() }}</strong> invoices
                </p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Account</th>
                            <th>Period</th>
                            <th>Issued</th>
                            <th>Total</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            @php $balance = $invoice->balanceDue(); @endphp
                            <tr class="ci-index-row" onclick="window.location='{{ route('customer-invoices.show', $invoice) }}'">
                                <td>
                                    <a href="{{ route('customer-invoices.show', $invoice) }}" class="ci-inv-num" onclick="event.stopPropagation()">{{ $invoice->invoice_number }}</a>
                                </td>
                                <td>
                                    @if ($invoice->account)
                                        <a href="{{ route('customer-accounts.show', $invoice->account) }}" class="ci-account-link" onclick="event.stopPropagation()">
                                            <span class="ci-account-icon"><i class="fas fa-bus"></i></span>
                                            <span class="ci-account-name">{{ $invoice->account->name }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-500">
                                    {{ $invoice->period_start->format('d M') }} – {{ $invoice->period_end->format('d M Y') }}
                                </td>
                                <td class="text-sm text-gray-500">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</td>
                                <td class="font-semibold">{{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    @if ($balance > 0)
                                        <span class="ci-balance-due">KES {{ number_format($balance, 2) }}</span>
                                    @else
                                        <span class="ci-balance-clear">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span>
                                    @if ($invoice->isOverdue())
                                        <span class="text-[0.6rem] font-bold text-rose-600 block mt-0.5">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customer-invoices.show', $invoice) }}" class="mi-action view" onclick="event.stopPropagation()">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="!py-16 text-center">
                                    <div class="ci-empty-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                    <p class="font-semibold text-gray-600">No invoices found</p>
                                    <p class="text-sm text-gray-400 mt-1">Generate your first monthly statement from uninvoiced credit sales.</p>
                                    @can('customer_invoices.manage')
                                        <a href="{{ route('customer-invoices.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                            <i class="fas fa-plus text-xs"></i> Generate Invoice
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="mi-card-foot">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
