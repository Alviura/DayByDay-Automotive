<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialPeriodService
{
    public function ensurePeriod(int $year, int $month): FinancialPeriod
    {
        return FinancialPeriod::firstOrCreate(
            ['period_year' => $year, 'period_month' => $month],
            ['status' => 'open']
        );
    }

    public function isDateOpen(Carbon|string $date): bool
    {
        $date = Carbon::parse($date);

        $period = FinancialPeriod::query()
            ->where('period_year', $date->year)
            ->where('period_month', $date->month)
            ->first();

        return ! $period || $period->status === 'open';
    }

    public function assertDateOpen(Carbon|string $date): void
    {
        if (! $this->isDateOpen($date)) {
            $date = Carbon::parse($date);

            throw new \InvalidArgumentException(
                'Accounting period '.$date->format('F Y').' is closed. Reopen the period or choose a different date.'
            );
        }
    }

    public function close(int $year, int $month, ?User $user = null, ?string $notes = null): FinancialPeriod
    {
        $period = $this->ensurePeriod($year, $month);

        if ($period->isClosed()) {
            throw new \InvalidArgumentException('This period is already closed.');
        }

        $period->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $user?->id,
            'notes' => $notes,
        ]);

        return $period->fresh(['closer']);
    }

    public function reopen(int $year, int $month): FinancialPeriod
    {
        $period = $this->ensurePeriod($year, $month);

        if (! $period->isClosed()) {
            throw new \InvalidArgumentException('This period is already open.');
        }

        $period->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
            'notes' => null,
        ]);

        return $period->fresh();
    }

    /**
     * @return Collection<int, FinancialPeriod>
     */
    public function recentPeriods(int $months = 12): Collection
    {
        $periods = collect();

        for ($i = 0; $i < $months; $i++) {
            $date = now()->subMonths($i);
            $periods->push($this->ensurePeriod($date->year, $date->month));
        }

        return $periods;
    }
}
