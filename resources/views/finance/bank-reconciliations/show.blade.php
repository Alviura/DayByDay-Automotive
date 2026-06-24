<x-app-layout title="Bank Reconciliation">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-building-columns"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $recon->account->name }}</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Statement {{ $recon->statement_date->format('d M Y') }} · {{ $recon->account->code }}</p>
                </div>
            </div>
            <a href="{{ route('bank-reconciliations.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'bank-recon'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Statement Balance</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($recon->statement_balance, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-file-lines"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Book Balance</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($bookBalance, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-book"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Adjusted Book</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($recon->isReconciled() ? $recon->adjusted_balance : $adjusted, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-sliders"></i></div>
            </div>
            <div class="mi-kpi {{ abs($recon->isReconciled() ? $recon->difference : $difference) < 0.01 ? 'mi-kpi-green' : 'mi-kpi-amber' }}">
                <div>
                    <p class="mi-kpi-label">Difference</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($recon->isReconciled() ? $recon->difference : $difference, 2) }}</p>
                    <p class="fin-kpi-sub">{{ $recon->isReconciled() ? 'Reconciled' : 'Outstanding items' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-scale-balanced"></i></div>
            </div>
        </div>

        @if (! $recon->isReconciled())
            <div class="fin-banner {{ abs($difference) < 0.01 ? 'fin-banner-balanced' : 'fin-banner-unbalanced' }}">
                <i class="fas {{ abs($difference) < 0.01 ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                <span>
                    Outstanding debits: KES {{ number_format($outstandingDebits, 2) }} ·
                    Outstanding credits: KES {{ number_format($outstandingCredits, 2) }}.
                    @if (abs($difference) < 0.01)
                        Ready to reconcile.
                    @else
                        Select cleared items below until the difference is zero.
                    @endif
                </span>
            </div>
        @endif

        <div class="fin-doc-card">
            <div class="fin-doc-head">
                <h2>{{ $recon->isReconciled() ? 'Cleared Items' : 'Uncleared Transactions' }}</h2>
                <span>{{ $recon->isReconciled() ? $recon->items->count() : $uncleared->count() }} items</span>
            </div>

            @if ($recon->isReconciled())
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Journal</th>
                            <th>Description</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recon->items as $item)
                            @php $line = $item->journalLine; $entry = $line->journalEntry; @endphp
                            <tr>
                                <td>{{ $entry->entry_date->format('d M Y') }}</td>
                                <td><a href="{{ route('journal-entries.show', $entry) }}" class="text-orange-600 hover:underline fin-mono text-xs">{{ $entry->entry_number }}</a></td>
                                <td>{{ $line->description ?? $entry->description }}</td>
                                <td class="text-right fin-tb-debit">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                <td class="text-right fin-tb-credit">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <form method="POST" action="{{ route('bank-reconciliations.complete', $recon) }}">
                    @csrf
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th class="w-10"></th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Description</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($uncleared as $line)
                                @php $entry = $line->journalEntry; @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" name="journal_line_ids[]" value="{{ $line->id }}" class="rounded border-gray-300">
                                    </td>
                                    <td>{{ $entry->entry_date->format('d M Y') }}</td>
                                    <td class="fin-mono text-xs">{{ $entry->entry_number }}</td>
                                    <td>{{ $line->description ?? $entry->description }}</td>
                                    <td class="text-right fin-tb-debit">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                    <td class="text-right fin-tb-credit">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-gray-500 text-sm p-4">All transactions are cleared.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @can('finance.manage')
                        <div class="p-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-3">Check transactions that appear on the bank statement, then complete reconciliation.</p>
                            <button type="submit" class="mi-btn-orange">
                                <i class="fas fa-check text-xs"></i> Complete Reconciliation
                            </button>
                        </div>
                    @endcan
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
