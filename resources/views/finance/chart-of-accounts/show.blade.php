<x-app-layout :title="$chartOfAccount->code">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3">
                @include('finance.partials.account-icon', ['account' => $chartOfAccount, 'size' => 'lg'])
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold fin-mono text-gray-900">{{ $chartOfAccount->code }}</h1>
                        <span class="fin-type-pill {{ $chartOfAccount->typePillClass() }}">
                            <i class="fas {{ $chartOfAccount->typeIcon() }}"></i> {{ $chartOfAccount->typeLabel() }}
                        </span>
                        @if ($chartOfAccount->is_system)
                            <span class="fin-badge fin-badge-indigo">System</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm font-semibold text-gray-700">{{ $chartOfAccount->name }}</p>
                    @if ($chartOfAccount->description)
                        <p class="text-xs text-gray-500 mt-1">{{ $chartOfAccount->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Print</button>
                @can('finance.manage')
                    <a href="{{ route('chart-of-accounts.edit', $chartOfAccount) }}" class="mi-btn-ghost">Edit</a>
                @endcan
                <a href="{{ route('chart-of-accounts.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> COA</a>
            </div>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'coa'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Closing Balance</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($balance, 2) }}</p>
                    <p class="fin-kpi-sub">As of {{ $to->format('d M Y') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Opening Balance</p>
                    <p class="mi-kpi-value fin-amt text-base">{{ number_format($openingBalance, 2) }}</p>
                    <p class="fin-kpi-sub">Before {{ $from->format('d M Y') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-door-open"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Period Debits</p>
                    <p class="mi-kpi-value fin-amt text-base" style="color:#15803d">{{ number_format($periodDebit, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-arrow-down"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Period Credits</p>
                    <p class="mi-kpi-value fin-amt text-base" style="color:#1d4ed8">{{ number_format($periodCredit, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>

        <div class="fin-show-grid">
            <div class="fin-doc-card">
                <div class="fin-doc-head flex flex-wrap items-center justify-between gap-3 no-print">
                    <div>
                        <h2 class="font-bold text-gray-900">Account Ledger</h2>
                        <p class="text-xs text-gray-500">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
                    </div>
                    <form method="GET" class="flex flex-wrap gap-2 items-end">
                        <div>
                            <label class="mi-field-label text-xs">From</label>
                            <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="mi-input text-sm">
                        </div>
                        <div>
                            <label class="mi-field-label text-xs">To</label>
                            <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="mi-input text-sm">
                        </div>
                        <button type="submit" class="mi-btn-orange text-sm">Update</button>
                    </form>
                </div>

                <div class="fin-doc-body">
                    @if ($ledger->isEmpty())
                        <div class="fin-empty">
                            <div class="fin-empty-icon"><i class="fas fa-book-open"></i></div>
                            <p class="font-semibold text-gray-700">No posted activity</p>
                            <p class="text-sm text-gray-500 mt-1">This account has no journal lines in the selected period.</p>
                        </div>
                    @else
                        @php $runBal = $openingBalance; @endphp
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Journal</th>
                                    <th>Description</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ledger as $row)
                                    @php
                                        $line = $row['line'];
                                        $entry = $row['entry'];
                                        $delta = (float) $line->debit - (float) $line->credit;
                                        if ($chartOfAccount->normal_balance->value === 'credit') {
                                            $delta = -$delta;
                                        }
                                        $runBal += $delta;
                                    @endphp
                                    <tr>
                                        <td class="text-sm whitespace-nowrap">{{ $entry->entry_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('journal-entries.show', $entry) }}" class="fin-entry-chip fin-mono text-orange-600 hover:border-orange-300" onclick="event.stopPropagation()">
                                                <i class="fas fa-book"></i> {{ $entry->entry_number }}
                                            </a>
                                        </td>
                                        <td class="text-sm text-gray-600 max-w-xs truncate">{{ $line->description ?? $entry->description }}</td>
                                        <td class="text-right fin-tb-debit">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                        <td class="text-right fin-tb-credit">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                                        <td class="text-right fin-tb-balance fin-amt">{{ number_format($runBal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="fin-doc-foot">
                            <span class="fin-tb-debit">Dr {{ number_format($periodDebit, 2) }}</span>
                            <span class="fin-tb-credit">Cr {{ number_format($periodCredit, 2) }}</span>
                            <span class="fin-tb-balance">Balance {{ number_format($balance, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <aside class="mi-guide no-print">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Account Details</h2>
                        <p class="mi-guide-subtitle">Dimensions &amp; links</p>
                    </div>
                </div>
                <div class="mi-guide-body">
                    <ul class="mi-show-meta">
                        <li>
                            <span class="mi-show-meta-label">Normal balance</span>
                            <span class="mi-show-meta-value capitalize">{{ $chartOfAccount->normal_balance->value }}</span>
                        </li>
                        @if ($chartOfAccount->shop)
                            <li>
                                <span class="mi-show-meta-label">Shop</span>
                                <span class="mi-show-meta-value">{{ $chartOfAccount->shop->name }}</span>
                            </li>
                        @endif
                        @if ($chartOfAccount->payment_method)
                            <li>
                                <span class="mi-show-meta-label">Payment method</span>
                                <span class="mi-show-meta-value">{{ \App\Models\Payment::methods()[$chartOfAccount->payment_method] ?? $chartOfAccount->payment_method }}</span>
                            </li>
                        @endif
                    </ul>
                    <div class="mi-show-actions mt-4">
                        <a href="{{ route('trial-balance.index') }}" class="mi-btn-ghost w-full justify-center">
                            <i class="fas fa-scale-balanced text-xs"></i> Trial Balance
                        </a>
                        <a href="{{ route('journal-entries.index') }}" class="mi-btn-ghost w-full justify-center">
                            <i class="fas fa-book text-xs"></i> All Journals
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
