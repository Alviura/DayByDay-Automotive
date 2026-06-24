<?php

namespace App\Services;

use App\Enums\JournalEntryStatus;
use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TrialBalanceService
{
    /**
     * @return Collection<int, array{account: ChartOfAccount, debit: float, credit: float, balance: float}>
     */
    public function forPeriod(?Carbon $from = null, ?Carbon $to = null, bool $activeOnly = true): Collection
    {
        $to ??= now();
        $from ??= $to->copy()->startOfMonth();

        $query = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::Posted)
            ->whereBetween('journal_entries.entry_date', [
                $from->toDateString(),
                $to->toDateString(),
            ]);

        $totals = $query
            ->selectRaw('journal_lines.chart_of_account_id as account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit')
            ->selectRaw('SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.chart_of_account_id')
            ->get()
            ->keyBy('account_id');

        $accountsQuery = ChartOfAccount::query()->orderBy('code');

        if ($activeOnly) {
            $accountsQuery->active();
        }

        return $accountsQuery->get()->map(function (ChartOfAccount $account) use ($totals) {
            $row = $totals->get($account->id);
            $debit = (float) ($row->total_debit ?? 0);
            $credit = (float) ($row->total_credit ?? 0);

            return [
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $account->signedBalance($debit, $credit),
            ];
        })->filter(fn (array $row) => $row['debit'] > 0 || $row['credit'] > 0 || $row['balance'] != 0);
    }

    public function totals(Collection $rows): array
    {
        return [
            'debit' => round($rows->sum('debit'), 2),
            'credit' => round($rows->sum('credit'), 2),
        ];
    }

    public function accountLedger(ChartOfAccount $account, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $to ??= now();
        $from ??= $to->copy()->subMonths(3);

        return JournalLine::query()
            ->with(['journalEntry.poster'])
            ->where('chart_of_account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($from, $to) {
                $q->where('status', JournalEntryStatus::Posted)
                    ->whereBetween('entry_date', [$from->toDateString(), $to->toDateString()]);
            })
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_lines.id')
            ->select('journal_lines.*')
            ->get()
            ->map(function (JournalLine $line) use ($account) {
                return [
                    'line' => $line,
                    'entry' => $line->journalEntry,
                    'running_balance' => null,
                ];
            });
    }

    public function accountBalance(ChartOfAccount $account, ?Carbon $asOf = null): float
    {
        $asOf ??= now();

        $totals = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.chart_of_account_id', $account->id)
            ->where('journal_entries.status', JournalEntryStatus::Posted)
            ->where('journal_entries.entry_date', '<=', $asOf->toDateString())
            ->selectRaw('SUM(journal_lines.debit) as total_debit')
            ->selectRaw('SUM(journal_lines.credit) as total_credit')
            ->first();

        return $account->signedBalance(
            (float) ($totals->total_debit ?? 0),
            (float) ($totals->total_credit ?? 0)
        );
    }

    /**
     * Cumulative balances for all accounts as of a date.
     *
     * @return \Illuminate\Support\Collection<int, array{account: ChartOfAccount, debit: float, credit: float, balance: float}>
     */
    public function balancesAsOf(?Carbon $asOf = null, bool $activeOnly = true): \Illuminate\Support\Collection
    {
        $asOf ??= now();

        $query = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::Posted)
            ->where('journal_entries.entry_date', '<=', $asOf->toDateString());

        $totals = $query
            ->selectRaw('journal_lines.chart_of_account_id as account_id')
            ->selectRaw('SUM(journal_lines.debit) as total_debit')
            ->selectRaw('SUM(journal_lines.credit) as total_credit')
            ->groupBy('journal_lines.chart_of_account_id')
            ->get()
            ->keyBy('account_id');

        $accountsQuery = ChartOfAccount::query()->orderBy('code');

        if ($activeOnly) {
            $accountsQuery->active();
        }

        return $accountsQuery->get()->map(function (ChartOfAccount $account) use ($totals) {
            $row = $totals->get($account->id);
            $debit = (float) ($row->total_debit ?? 0);
            $credit = (float) ($row->total_credit ?? 0);

            return [
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $account->signedBalance($debit, $credit),
            ];
        })->filter(fn (array $row) => $row['debit'] > 0 || $row['credit'] > 0 || $row['balance'] != 0);
    }
}
