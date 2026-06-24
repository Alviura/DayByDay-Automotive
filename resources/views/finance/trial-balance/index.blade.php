<x-app-layout title="Trial Balance">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-scale-balanced"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Trial Balance</h1>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
                </div>
            </div>
            <button type="button" onclick="window.print()" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Print</button>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'trial-balance'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Accounts with Activity</p>
                    <p class="mi-kpi-value">{{ $accountCount }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Total Debits</p>
                    <p class="mi-kpi-value fin-amt" style="color:#15803d">{{ number_format($totals['debit'], 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-arrow-down"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Total Credits</p>
                    <p class="mi-kpi-value fin-amt" style="color:#1d4ed8">{{ number_format($totals['credit'], 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-arrow-up"></i></div>
            </div>
            <div class="mi-kpi {{ $isBalanced ? 'mi-kpi-green' : 'mi-kpi-amber' }}">
                <div>
                    <p class="mi-kpi-label">Difference</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}</p>
                    <p class="fin-kpi-sub">{{ $isBalanced ? 'In balance' : 'Out of balance' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-scale-balanced"></i></div>
            </div>
        </div>

        @if ($rows->isNotEmpty())
            <div class="fin-banner {{ $isBalanced ? 'fin-banner-balanced' : 'fin-banner-unbalanced' }} no-print">
                <i class="fas {{ $isBalanced ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                <span>
                    @if ($isBalanced)
                        Trial balance is <strong>in balance</strong> — total debits equal total credits for this period.
                    @else
                        Trial balance is <strong>out of balance</strong> by KES {{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}.
                    @endif
                </span>
            </div>
        @endif

        <div class="mi-card p-4 no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="mi-field-label">Period from</label>
                    <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="mi-input">
                </div>
                <div>
                    <label class="mi-field-label">Period to</label>
                    <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="mi-input">
                </div>
                <button type="submit" class="mi-btn-orange">Run Report</button>
                <a href="{{ route('trial-balance.index', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="mi-btn-ghost text-sm">This month</a>
            </form>
        </div>

        <div class="fin-doc-card">
            @if ($rows->isEmpty())
                <div class="fin-empty">
                    <div class="fin-empty-icon"><i class="fas fa-scale-balanced"></i></div>
                    <p class="font-semibold text-gray-700">No GL activity in this period</p>
                    <p class="text-sm text-gray-500 mt-1">Post journal entries or complete F2 automated posting to see balances here.</p>
                </div>
            @else
                <div class="fin-doc-body">
                    @foreach ($typeOrder as $typeKey)
                        @if (! isset($grouped[$typeKey]) || $grouped[$typeKey]->isEmpty())
                            @continue
                        @endif
                        @php
                            $type = \App\Enums\AccountType::from($typeKey);
                            $groupRows = $grouped[$typeKey];
                            $groupDebit = $groupRows->sum('debit');
                            $groupCredit = $groupRows->sum('credit');
                        @endphp
                        <div class="fin-tb-group">
                            <div class="fin-section-head">
                                <span><i class="fas {{ $type->icon() }} mr-1"></i> {{ $type->label() }}s</span>
                                <span class="fin-type-pill {{ $type->pillClass() }}">{{ $groupRows->count() }} accounts</span>
                            </div>
                            <table class="mi-table">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Credit</th>
                                        <th class="text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groupRows as $row)
                                        <tr class="fin-index-row" onclick="window.location='{{ route('chart-of-accounts.show', $row['account']) }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}'">
                                            <td>
                                                <div class="fin-acct-cell">
                                                    @include('finance.partials.account-icon', ['account' => $row['account']])
                                                    <div>
                                                        <p class="fin-acct-name">{{ $row['account']->name }}</p>
                                                        <p class="fin-mono text-xs text-gray-500">{{ $row['account']->code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-right fin-tb-debit">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                                            <td class="text-right fin-tb-credit">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                                            <td class="text-right fin-tb-balance fin-amt {{ $row['balance'] < 0 ? 'negative' : '' }}">{{ number_format($row['balance'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 font-semibold text-sm">
                                        <td class="text-right text-gray-500">{{ $type->label() }} subtotal</td>
                                        <td class="text-right fin-tb-debit">{{ number_format($groupDebit, 2) }}</td>
                                        <td class="text-right fin-tb-credit">{{ number_format($groupCredit, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endforeach
                    <div class="fin-doc-foot text-base">
                        <span class="fin-tb-debit">Total Dr {{ number_format($totals['debit'], 2) }}</span>
                        <span class="fin-tb-credit">Total Cr {{ number_format($totals['credit'], 2) }}</span>
                        @if (! $isBalanced)
                            <span class="text-rose-600">Diff {{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
