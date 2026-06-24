<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    public function __construct(private TrialBalanceService $trialBalance) {}

    /**
     * @return Collection<int, ChartOfAccount>
     */
    public function bankAccounts(): Collection
    {
        $bankCode = config('finance.accounts.bank');

        return ChartOfAccount::query()
            ->active()
            ->where('account_type', AccountType::Asset)
            ->where(function ($q) use ($bankCode) {
                $q->where('code', $bankCode)
                    ->orWhere('code', 'like', config('finance.cash_account_prefix').'-%');
            })
            ->orderBy('code')
            ->get();
    }

    public function createDraft(
        ChartOfAccount $account,
        Carbon $statementDate,
        float $statementBalance,
        ?User $user = null,
        ?string $notes = null
    ): BankReconciliation {
        return BankReconciliation::create([
            'chart_of_account_id' => $account->id,
            'statement_date' => $statementDate->toDateString(),
            'statement_balance' => $statementBalance,
            'status' => 'draft',
            'notes' => $notes,
            'created_by' => $user?->id,
        ]);
    }

    public function bookBalance(ChartOfAccount $account, Carbon $asOf): float
    {
        return $this->trialBalance->accountBalance($account, $asOf);
    }

    /**
     * @return Collection<int, JournalLine>
     */
    public function unclearedLines(ChartOfAccount $account, Carbon $asOf): Collection
    {
        $clearedLineIds = BankReconciliationItem::query()
            ->whereHas('reconciliation', fn ($q) => $q->where('status', 'reconciled'))
            ->pluck('journal_line_id');

        return JournalLine::query()
            ->with(['journalEntry'])
            ->where('chart_of_account_id', $account->id)
            ->whereNotIn('journal_lines.id', $clearedLineIds)
            ->whereHas('journalEntry', function ($q) use ($asOf) {
                $q->where('status', JournalEntryStatus::Posted)
                    ->where('entry_date', '<=', $asOf->toDateString());
            })
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_lines.id')
            ->select('journal_lines.*')
            ->get();
    }

    /**
     * @param  array<int, int>  $journalLineIds
     */
    public function reconcile(BankReconciliation $recon, array $journalLineIds, ?User $user = null): BankReconciliation
    {
        if ($recon->isReconciled()) {
            throw new \InvalidArgumentException('This reconciliation is already complete.');
        }

        $user ??= auth()->user();
        $recon->load('account');
        $account = $recon->account;
        $asOf = Carbon::parse($recon->statement_date);

        return DB::transaction(function () use ($recon, $journalLineIds, $user, $account, $asOf) {
            $allUncleared = $this->unclearedLines($account, $asOf);

            foreach ($journalLineIds as $lineId) {
                if (! $allUncleared->contains('id', (int) $lineId)) {
                    throw new \InvalidArgumentException('One or more selected lines are not available for reconciliation.');
                }
            }

            $recon->items()->delete();

            foreach ($journalLineIds as $lineId) {
                BankReconciliationItem::create([
                    'bank_reconciliation_id' => $recon->id,
                    'journal_line_id' => $lineId,
                ]);
            }

            $bookBalance = $this->bookBalance($account, $asOf);
            $outstanding = $allUncleared->reject(fn (JournalLine $line) => in_array($line->id, $journalLineIds, true));
            $outstandingCredits = round($outstanding->sum('credit'), 2);
            $outstandingDebits = round($outstanding->sum('debit'), 2);
            $adjusted = round($bookBalance - $outstandingCredits + $outstandingDebits, 2);
            $difference = round((float) $recon->statement_balance - $adjusted, 2);

            if (abs($difference) >= 0.01) {
                throw new \InvalidArgumentException(
                    'Reconciliation does not balance. Adjusted book: KES '.number_format($adjusted, 2).
                    ' vs statement: KES '.number_format((float) $recon->statement_balance, 2).
                    ' (difference KES '.number_format($difference, 2).').'
                );
            }

            $recon->update([
                'book_balance' => $bookBalance,
                'adjusted_balance' => $adjusted,
                'difference' => $difference,
                'status' => 'reconciled',
                'reconciled_by' => $user?->id,
                'reconciled_at' => now(),
            ]);

            return $recon->fresh(['account', 'items.journalLine.journalEntry', 'reconciler', 'creator']);
        });
    }
}
