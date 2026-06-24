<x-app-layout title="Financial Statements">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-chart-pie"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Financial Statements</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Profit &amp; Loss and Balance Sheet from the general ledger.</p>
                </div>
            </div>
            <button type="button" onclick="window.print()" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Print</button>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'statements'])

        <div class="fin-tab-bar no-print">
            <a href="{{ route('financial-statements.index', ['tab' => 'profit-loss', 'date_from' => $from->toDateString(), 'date_to' => $to->toDateString()]) }}"
               class="fin-tab {{ $tab === 'profit-loss' ? 'active' : '' }}">Profit &amp; Loss</a>
            <a href="{{ route('financial-statements.index', ['tab' => 'balance-sheet', 'as_of' => $asOf->toDateString()]) }}"
               class="fin-tab {{ $tab === 'balance-sheet' ? 'active' : '' }}">Balance Sheet</a>
        </div>

        @if ($tab === 'profit-loss')
            <div class="mi-card p-4 no-print">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="tab" value="profit-loss">
                    <div>
                        <label class="mi-field-label">From</label>
                        <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="mi-input">
                    </div>
                    <div>
                        <label class="mi-field-label">To</label>
                        <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="mi-input">
                    </div>
                    <button type="submit" class="mi-btn-orange">Run Report</button>
                </form>
            </div>

            <div class="fin-doc-card">
                <div class="fin-doc-head">
                    <h2>Profit &amp; Loss</h2>
                    <span>{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</span>
                </div>
                <div class="fin-doc-body">
                    <div class="fin-tb-group">
                        <div class="fin-section-head"><span><i class="fas fa-chart-line mr-1"></i> Revenue</span></div>
                        <table class="mi-table">
                            <tbody>
                                @forelse ($profitAndLoss['revenue'] as $row)
                                    <tr>
                                        <td><span class="fin-mono text-xs text-gray-500">{{ $row['account']->code }}</span> {{ $row['account']->name }}</td>
                                        <td class="text-right fin-amt">{{ number_format($row['balance'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-gray-500 text-sm">No revenue in period</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td>Total Revenue</td>
                                    <td class="text-right fin-amt text-green-700">{{ number_format($profitAndLoss['total_revenue'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="fin-tb-group">
                        <div class="fin-section-head"><span><i class="fas fa-receipt mr-1"></i> Expenses</span></div>
                        <table class="mi-table">
                            <tbody>
                                @forelse ($profitAndLoss['expenses'] as $row)
                                    <tr>
                                        <td><span class="fin-mono text-xs text-gray-500">{{ $row['account']->code }}</span> {{ $row['account']->name }}</td>
                                        <td class="text-right fin-amt">{{ number_format($row['balance'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-gray-500 text-sm">No expenses in period</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td>Total Expenses</td>
                                    <td class="text-right fin-amt text-rose-700">{{ number_format($profitAndLoss['total_expenses'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="fin-doc-foot text-base font-bold">
                        <span>Net Income</span>
                        <span class="fin-amt {{ $profitAndLoss['net_income'] < 0 ? 'negative' : '' }}">{{ number_format($profitAndLoss['net_income'], 2) }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="mi-card p-4 no-print">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="tab" value="balance-sheet">
                    <div>
                        <label class="mi-field-label">As of</label>
                        <input type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="mi-input">
                    </div>
                    <button type="submit" class="mi-btn-orange">Run Report</button>
                </form>
            </div>

            @if (! $isBalanced)
                <div class="fin-banner fin-banner-unbalanced no-print">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>Balance sheet is out of balance by KES {{ number_format(abs($balanceSheet['total_assets'] - $balanceSheet['total_liabilities_equity']), 2) }}.</span>
                </div>
            @endif

            <div class="fin-doc-card">
                <div class="fin-doc-head">
                    <h2>Balance Sheet</h2>
                    <span>As of {{ $asOf->format('d M Y') }}</span>
                </div>
                <div class="fin-doc-body space-y-6">
                    @foreach ([
                        ['label' => 'Assets', 'icon' => 'fa-coins', 'rows' => $balanceSheet['assets'], 'total' => $balanceSheet['total_assets']],
                        ['label' => 'Liabilities', 'icon' => 'fa-hand-holding-dollar', 'rows' => $balanceSheet['liabilities'], 'total' => $balanceSheet['total_liabilities']],
                    ] as $section)
                        <div class="fin-tb-group">
                            <div class="fin-section-head"><span><i class="fas {{ $section['icon'] }} mr-1"></i> {{ $section['label'] }}</span></div>
                            <table class="mi-table">
                                <tbody>
                                    @forelse ($section['rows'] as $row)
                                        <tr>
                                            <td><span class="fin-mono text-xs text-gray-500">{{ $row['account']->code }}</span> {{ $row['account']->name }}</td>
                                            <td class="text-right fin-amt">{{ number_format($row['balance'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-gray-500 text-sm">No {{ strtolower($section['label']) }}</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td>Total {{ $section['label'] }}</td>
                                        <td class="text-right fin-amt">{{ number_format($section['total'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endforeach

                    <div class="fin-tb-group">
                        <div class="fin-section-head"><span><i class="fas fa-landmark mr-1"></i> Equity</span></div>
                        <table class="mi-table">
                            <tbody>
                                @foreach ($balanceSheet['equity'] as $row)
                                    <tr>
                                        <td><span class="fin-mono text-xs text-gray-500">{{ $row['account']->code }}</span> {{ $row['account']->name }}</td>
                                        <td class="text-right fin-amt">{{ number_format($row['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>Current year earnings (YTD net income)</td>
                                    <td class="text-right fin-amt">{{ number_format($balanceSheet['net_income_ytd'], 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td>Total Equity</td>
                                    <td class="text-right fin-amt">{{ number_format($balanceSheet['total_equity'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="fin-doc-foot text-base font-bold">
                        <span>Total Liabilities &amp; Equity</span>
                        <span class="fin-amt">{{ number_format($balanceSheet['total_liabilities_equity'], 2) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
