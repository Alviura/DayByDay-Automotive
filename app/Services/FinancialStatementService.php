<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialStatementService
{
    public function __construct(private TrialBalanceService $trialBalance) {}

    /**
     * @return array{
     *     revenue: Collection,
     *     expenses: Collection,
     *     total_revenue: float,
     *     total_expenses: float,
     *     net_income: float
     * }
     */
    public function profitAndLoss(Carbon $from, Carbon $to): array
    {
        $rows = $this->trialBalance->forPeriod($from, $to);

        $revenue = $rows
            ->filter(fn (array $row) => $row['account']->account_type === AccountType::Revenue)
            ->sortBy(fn (array $row) => $row['account']->code)
            ->values();

        $expenses = $rows
            ->filter(fn (array $row) => $row['account']->account_type === AccountType::Expense)
            ->sortBy(fn (array $row) => $row['account']->code)
            ->values();

        $totalRevenue = round($revenue->sum(fn (array $row) => $row['balance']), 2);
        $totalExpenses = round($expenses->sum(fn (array $row) => $row['balance']), 2);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => round($totalRevenue - $totalExpenses, 2),
        ];
    }

    /**
     * @return array{
     *     assets: Collection,
     *     liabilities: Collection,
     *     equity: Collection,
     *     total_assets: float,
     *     total_liabilities: float,
     *     total_equity: float,
     *     net_income_ytd: float,
     *     total_liabilities_equity: float
     * }
     */
    public function balanceSheet(Carbon $asOf): array
    {
        $yearStart = $asOf->copy()->startOfYear();
        $rows = $this->trialBalance->balancesAsOf($asOf);

        $assets = $rows
            ->filter(fn (array $row) => $row['account']->account_type === AccountType::Asset)
            ->sortBy(fn (array $row) => $row['account']->code)
            ->values();

        $liabilities = $rows
            ->filter(fn (array $row) => $row['account']->account_type === AccountType::Liability)
            ->sortBy(fn (array $row) => $row['account']->code)
            ->values();

        $equity = $rows
            ->filter(fn (array $row) => $row['account']->account_type === AccountType::Equity)
            ->sortBy(fn (array $row) => $row['account']->code)
            ->values();

        $netIncomeYtd = $this->profitAndLoss($yearStart, $asOf)['net_income'];

        $totalAssets = round($assets->sum(fn (array $row) => $row['balance']), 2);
        $totalLiabilities = round($liabilities->sum(fn (array $row) => $row['balance']), 2);
        $totalEquity = round($equity->sum(fn (array $row) => $row['balance']) + $netIncomeYtd, 2);

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'net_income_ytd' => $netIncomeYtd,
            'total_liabilities_equity' => round($totalLiabilities + $totalEquity, 2),
        ];
    }
}
