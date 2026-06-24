<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\ChartOfAccount;
use App\Models\TaxRemittance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaxRemittanceService
{
    public function __construct(
        private AccountingService $accounting,
        private TrialBalanceService $trialBalance,
    ) {}

    public function ensurePeriod(int $year, int $month, ?User $user = null): TaxRemittance
    {
        $dueDay = (int) config('sales.vat.remittance_due_day', 20);
        $dueDate = Carbon::create($year, $month, 1)->addMonth()->day(min($dueDay, 28));

        $remittance = TaxRemittance::firstOrCreate(
            ['period_year' => $year, 'period_month' => $month],
            [
                'status' => 'open',
                'due_date' => $dueDate,
                'created_by' => $user?->id,
            ]
        );

        return $this->syncTaxCollected($remittance);
    }

    public function syncTaxCollected(TaxRemittance $remittance): TaxRemittance
    {
        if ($remittance->status === 'paid') {
            return $remittance;
        }

        $from = Carbon::create($remittance->period_year, $remittance->period_month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();
        $vatAccount = $this->accounting->findAccountByCode(config('finance.accounts.vat_payable'));

        $row = $this->trialBalance->forPeriod($from, $to, activeOnly: false)
            ->firstWhere(fn (array $r) => $r['account']->id === $vatAccount->id);

        $collected = $row ? max(0, round($row['balance'], 2)) : 0;

        $remittance->update([
            'tax_collected' => max(0, round($collected, 2)),
        ]);

        return $remittance->fresh(['creator']);
    }

    public function file(TaxRemittance $remittance, ?User $user = null): TaxRemittance
    {
        if ($remittance->status !== 'open') {
            throw new \InvalidArgumentException('Only open VAT periods can be filed.');
        }

        $this->syncTaxCollected($remittance);

        $remittance->update([
            'status' => 'filed',
            'filed_at' => now(),
        ]);

        return $remittance->fresh(['creator']);
    }

    public function markPaid(TaxRemittance $remittance, float $amount, ?User $user = null): TaxRemittance
    {
        $user ??= auth()->user();

        if (! in_array($remittance->status, ['open', 'filed'], true)) {
            throw new \InvalidArgumentException('This VAT period cannot be marked paid.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Remittance amount must be greater than zero.');
        }

        $this->syncTaxCollected($remittance);

        return DB::transaction(function () use ($remittance, $amount, $user) {
            $this->accounting->post(
                'tax_remittance.paid',
                $remittance,
                [
                    [
                        'account_code' => config('finance.accounts.vat_payable'),
                        'debit' => $amount,
                        'description' => 'VAT remittance',
                    ],
                    [
                        'account_code' => config('finance.accounts.bank'),
                        'credit' => $amount,
                        'description' => 'VAT payment to KRA',
                    ],
                ],
                now(),
                'VAT remittance '.$remittance->periodLabel(),
                $user
            );

            $newRemitted = round((float) $remittance->amount_remitted + $amount, 2);

            $remittance->update([
                'amount_remitted' => $newRemitted,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $remittance->fresh(['creator']);
        });
    }
}
