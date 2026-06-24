<x-app-layout title="Chart of Accounts">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','type','status']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-sitemap"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Chart of Accounts</h1>
                    <p class="mt-0.5 text-sm text-gray-500">General ledger master — assets, liabilities, revenue &amp; expense.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 no-print">
                @can('finance.manage')
                    <a href="{{ route('chart-of-accounts.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i> New Account
                    </a>
                @endcan
            </div>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'coa'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Active Accounts</p>
                    <p class="mi-kpi-value">{{ number_format($stats['active']) }}</p>
                    <p class="fin-kpi-sub">{{ $stats['total'] }} total in master</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Assets</p>
                    <p class="mi-kpi-value">{{ $stats['assets'] }}</p>
                    <p class="fin-kpi-sub">Cash, AR, inventory…</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Liabilities</p>
                    <p class="mi-kpi-value">{{ $stats['liabilities'] }}</p>
                    <p class="fin-kpi-sub">AP, VAT, payroll…</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hand-holding-dollar"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">With Balance</p>
                    <p class="mi-kpi-value">{{ $stats['with_balance'] }}</p>
                    <p class="fin-kpi-sub">Non-zero GL balance</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-simple"></i></div>
            </div>
        </div>

        <div class="mi-card p-4 no-print">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Browse by type</p>
            <div class="fin-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $step['key'] === '' ? ! request('type') : request('type') === $step['key'];
                        $params = $step['key'] === ''
                            ? request()->except('page', 'type')
                            : array_merge(request()->except('page'), ['type' => $step['key']]);
                    @endphp
                    <a href="{{ route('chart-of-accounts.index', $params) }}" class="fin-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="fin-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="fin-pipe-count">{{ $step['count'] }}</span>
                        <span class="fin-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mi-card no-print">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if (request()->hasAny(['search','status']))
                        <span class="mi-cat-badge">Active</span>
                    @endif
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition>
                @if (request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Code or account name…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Status</label>
                        <select name="status" class="mi-select">
                            <option value="active" @selected(request('status', 'active') === 'active')>Active only</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                            <option value="all" @selected(request('status') === 'all')>All</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">Apply</button>
                    <a href="{{ route('chart-of-accounts.index') }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-table-card">
            @if ($accounts->isEmpty())
                <div class="fin-empty">
                    <div class="fin-empty-icon"><i class="fas fa-sitemap"></i></div>
                    <p class="font-semibold text-gray-700">No accounts match your filters</p>
                    <p class="text-sm text-gray-500 mt-1">Try a different type or search term.</p>
                </div>
            @else
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Normal</th>
                            <th class="text-right">Balance (KES)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            @php $bal = $balances[$account->id] ?? 0; @endphp
                            <tr class="fin-index-row" onclick="window.location='{{ route('chart-of-accounts.show', $account) }}'">
                                <td>
                                    <div class="fin-acct-cell">
                                        @include('finance.partials.account-icon', ['account' => $account])
                                        <div class="min-w-0">
                                            <p class="fin-acct-name truncate">{{ $account->name }}</p>
                                            <p class="fin-mono text-xs text-gray-500">{{ $account->code }}
                                                @if ($account->shop)
                                                    · {{ $account->shop->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fin-type-pill {{ $account->typePillClass() }}">
                                        <i class="fas {{ $account->typeIcon() }}"></i> {{ $account->typeLabel() }}
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500 capitalize">{{ $account->normal_balance->value }}</td>
                                <td class="text-right">
                                    <span class="fin-tb-balance fin-amt {{ $bal < 0 ? 'negative' : '' }}">{{ number_format($bal, 2) }}</span>
                                </td>
                                <td>
                                    @if ($account->is_active)
                                        <span class="fin-badge fin-badge-green">Active</span>
                                    @else
                                        <span class="fin-badge fin-badge-slate">Inactive</span>
                                    @endif
                                    @if ($account->is_system)
                                        <span class="fin-badge fin-badge-indigo ml-1">System</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mi-table-footer">{{ $accounts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
