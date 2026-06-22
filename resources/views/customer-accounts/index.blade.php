<x-app-layout title="Fleet Accounts">

    @push('styles')
        <x-module.page-index-styles />
        @include('customer-accounts.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','status','sort']) ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-bus"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Fleet Accounts</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Credit customers and PSV fleets billed on a monthly cycle.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('customer_invoices.view')
                    <a href="{{ route('customer-invoices.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-file-invoice-dollar text-xs"></i> Invoices
                    </a>
                @endcan
                @can('customer_accounts.manage')
                    <a href="{{ route('customer-accounts.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i> New Account
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Accounts</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="ca-kpi-sub">{{ $stats['active'] }} active · {{ $stats['inactive'] }} inactive</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Total Outstanding</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['total_outstanding'], 0) }}</p>
                    <p class="ca-kpi-sub">KES across all accounts</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Accounts Owing</p>
                    <p class="mi-kpi-value">{{ $stats['with_balance'] }}</p>
                    <p class="ca-kpi-sub">With unpaid balance</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Uninvoiced Sales</p>
                    <p class="mi-kpi-value">{{ $stats['uninvoiced_sales'] }}</p>
                    <p class="ca-kpi-sub">Awaiting monthly invoice</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>

        {{-- Pipeline --}}
        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Quick filter</p>
            <div class="ca-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = match ($step['key']) {
                            'active', 'inactive' => request('status') === $step['key'],
                            'with_balance' => request('filter') === 'with_balance',
                            default => ! request('status') && ! request('filter'),
                        };
                        $params = match ($step['key']) {
                            'active', 'inactive' => array_merge(request()->except('page', 'filter'), ['status' => $step['key']]),
                            'with_balance' => array_merge(request()->except('page', 'status'), ['filter' => 'with_balance']),
                            default => array_merge(request()->except('page', 'status', 'filter'), []),
                        };
                    @endphp
                    <a href="{{ route('customer-accounts.index', $params) }}"
                       class="ca-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="ca-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="ca-pipe-count">{{ $step['count'] }}</span>
                        <span class="ca-pipe-label">{{ $step['label'] }}</span>
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
                    @if (request()->hasAny(['search','status','sort']))
                        <span class="mi-cat-badge">Active</span>
                    @endif
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition>
                @if (request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Account, contact, phone…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-traffic-light"></i> Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active ({{ $stats['active'] }})</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive ({{ $stats['inactive'] }})</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Name A–Z</option>
                            <option value="balance" @selected(request('sort') === 'balance')>Highest balance</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('customer-accounts.index') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $accounts->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $accounts->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $accounts->total() }}</strong> accounts
                </p>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Contact</th>
                            <th>Terms</th>
                            <th>Outstanding</th>
                            <th>Uninvoiced</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            @php
                                $balance = (float) ($account->outstanding_balance ?? 0);
                                $creditPct = $account->credit_limit > 0
                                    ? min(100, round($balance / (float) $account->credit_limit * 100))
                                    : null;
                            @endphp
                            <tr class="ca-index-row" onclick="window.location='{{ route('customer-accounts.show', $account) }}'">
                                <td>
                                    <div class="ca-account-cell">
                                        <div class="ca-account-icon"><i class="fas fa-bus"></i></div>
                                        <div>
                                            <a href="{{ route('customer-accounts.show', $account) }}" class="ca-account-name" onclick="event.stopPropagation()">{{ $account->name }}</a>
                                            @if ($account->email)
                                                <p class="ca-account-sub">{{ $account->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($account->contact_name || $account->phone)
                                        <span class="text-sm text-gray-700">{{ $account->contact_name ?? '—' }}</span>
                                        @if ($account->phone)
                                            <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-phone text-[0.55rem]"></i> {{ $account->phone }}</p>
                                        @endif
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ca-terms-badge"><i class="fas fa-calendar-days text-[0.55rem]"></i> {{ $account->billing_terms }}</span>
                                </td>
                                <td>
                                    @if ($balance > 0)
                                        <span class="ca-balance ca-balance-due">KES {{ number_format($balance, 2) }}</span>
                                        @if ($creditPct !== null)
                                            <div class="ca-credit-bar" title="{{ $creditPct }}% of credit limit">
                                                <div class="ca-credit-fill {{ $creditPct >= 80 ? 'warn' : '' }}" style="width: {{ $creditPct }}%"></div>
                                            </div>
                                        @endif
                                    @else
                                        <span class="ca-balance ca-balance-clear">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($account->uninvoiced_count > 0)
                                        <span class="ca-uninvoiced">{{ $account->uninvoiced_count }} sale{{ $account->uninvoiced_count === 1 ? '' : 's' }}</span>
                                    @else
                                        <span class="text-gray-300 text-sm">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ca-badge {{ $account->is_active ? 'ca-badge-active' : 'ca-badge-inactive' }}">
                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('customer-accounts.show', $account) }}" class="mi-action view" title="View account" onclick="event.stopPropagation()">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!py-16 text-center">
                                    <div class="ca-empty-icon"><i class="fas fa-bus"></i></div>
                                    <p class="font-semibold text-gray-600">No fleet accounts found</p>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting filters or create a new account.</p>
                                    @can('customer_accounts.manage')
                                        <a href="{{ route('customer-accounts.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                            <i class="fas fa-plus text-xs"></i> New Account
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($accounts->hasPages())
                <div class="mi-card-foot">{{ $accounts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
