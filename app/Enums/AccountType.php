<?php

namespace App\Enums;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset => 'Asset',
            self::Liability => 'Liability',
            self::Equity => 'Equity',
            self::Revenue => 'Revenue',
            self::Expense => 'Expense',
        };
    }

    public function normalBalance(): NormalBalance
    {
        return match ($this) {
            self::Asset, self::Expense => NormalBalance::Debit,
            self::Liability, self::Equity, self::Revenue => NormalBalance::Credit,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Asset => 'fa-coins',
            self::Liability => 'fa-hand-holding-dollar',
            self::Equity => 'fa-landmark',
            self::Revenue => 'fa-chart-line',
            self::Expense => 'fa-receipt',
        };
    }

    public function pillClass(): string
    {
        return match ($this) {
            self::Asset => 'fin-type-asset',
            self::Liability => 'fin-type-liability',
            self::Equity => 'fin-type-equity',
            self::Revenue => 'fin-type-revenue',
            self::Expense => 'fin-type-expense',
        };
    }
}
