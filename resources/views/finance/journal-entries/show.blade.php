<x-app-layout :title="$journalEntry->entry_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-book"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold fin-mono text-gray-900">{{ $journalEntry->entry_number }}</h1>
                        <span class="{{ $journalEntry->status->badgeClass() }}">{{ $journalEntry->status->label() }}</span>
                        <span class="fin-badge {{ $journalEntry->source->value === 'manual' ? 'fin-badge-indigo' : 'fin-badge-blue' }}">{{ $journalEntry->source->label() }}</span>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-700">{{ $journalEntry->description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $journalEntry->entry_date->format('l, d M Y') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Print</button>
                <a href="{{ route('journal-entries.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Ledger</a>
            </div>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'ledger'])

        @if ($journalEntry->status === \App\Enums\JournalEntryStatus::Voided)
            <div class="fin-banner fin-banner-voided no-print">
                <i class="fas fa-ban"></i>
                <span>Voided {{ $journalEntry->voided_at?->format('d M Y H:i') }}
                    @if ($journalEntry->voidedBy) by {{ $journalEntry->voidedBy->name }} @endif
                    — {{ $journalEntry->void_reason }}</span>
            </div>
        @elseif ($journalEntry->isBalanced())
            <div class="fin-banner fin-banner-balanced no-print">
                <i class="fas fa-circle-check"></i>
                <span>Entry is balanced — KES {{ number_format($journalEntry->totalDebits(), 2) }} debits = credits</span>
            </div>
        @endif

        <div class="fin-show-grid">
            <div class="fin-doc-card">
                <div class="fin-doc-head">
                    <h2 class="font-bold text-gray-900">Journal Lines</h2>
                    <p class="text-xs text-gray-500">{{ $journalEntry->lines->count() }} account{{ $journalEntry->lines->count() === 1 ? '' : 's' }}</p>
                </div>
                <div class="fin-doc-body">
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Memo</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($journalEntry->lines as $line)
                                <tr>
                                    <td>
                                        <div class="fin-acct-cell">
                                            @include('finance.partials.account-icon', ['account' => $line->account])
                                            <div>
                                                <a href="{{ route('chart-of-accounts.show', $line->account) }}" class="fin-mono text-sm text-orange-600 hover:underline">{{ $line->account->code }}</a>
                                                <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $line->account->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">{{ $line->description ?: '—' }}</td>
                                    <td class="text-right fin-tb-debit">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                    <td class="text-right fin-tb-credit">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="fin-doc-foot">
                        <span class="fin-tb-debit">Dr {{ number_format($journalEntry->totalDebits(), 2) }}</span>
                        <span class="fin-tb-credit">Cr {{ number_format($journalEntry->totalCredits(), 2) }}</span>
                    </div>
                </div>
            </div>

            <aside class="mi-guide no-print">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Entry Info</h2>
                        <p class="mi-guide-subtitle">Audit trail</p>
                    </div>
                </div>
                <div class="mi-guide-body space-y-4">
                    <ul class="mi-show-meta">
                        @if ($journalEntry->creator)
                            <li>
                                <span class="mi-show-meta-label">Created by</span>
                                <span class="mi-show-meta-value">{{ $journalEntry->creator->name }}</span>
                            </li>
                        @endif
                        @if ($journalEntry->poster)
                            <li>
                                <span class="mi-show-meta-label">Posted by</span>
                                <span class="mi-show-meta-value">{{ $journalEntry->poster->name }}</span>
                                <span class="mi-show-meta-sub">{{ $journalEntry->posted_at?->format('d M Y H:i') }}</span>
                            </li>
                        @endif
                        @if ($journalEntry->event_type)
                            <li>
                                <span class="mi-show-meta-label">Event</span>
                                <span class="mi-show-meta-value mono text-xs">{{ $journalEntry->event_type }}</span>
                            </li>
                        @endif
                    </ul>

                    @if ($journalEntry->canSubmit())
                        @can('finance.journal')
                            <form method="POST" action="{{ route('journal-entries.submit', $journalEntry) }}">
                                @csrf
                                <button type="submit" class="mi-btn-orange w-full justify-center">
                                    <i class="fas fa-paper-plane text-xs"></i> Submit for Approval
                                </button>
                            </form>
                        @endcan
                    @endif

                    @if ($journalEntry->isApprovalPending() && $journalEntry->approval)
                        <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-900">
                            <i class="fas fa-hourglass-half mr-1"></i> Awaiting approval
                        </div>
                    @endif

                    @if ($journalEntry->canVoid())
                        @can('finance.manage')
                            <form method="POST" action="{{ route('journal-entries.void', $journalEntry) }}" class="space-y-2 pt-2 border-t"
                                  data-confirm="Void this journal? A reversing entry will be posted." data-confirm-variant="danger">
                                @csrf
                                <label class="mi-field-label text-rose-700">Void reason</label>
                                <textarea name="void_reason" rows="2" class="mi-input w-full" required placeholder="Why is this entry being voided?"></textarea>
                                <button type="submit" class="mi-btn-danger w-full justify-center">
                                    <i class="fas fa-ban text-xs"></i> Void Entry
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
