<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\ChartOfAccount;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (ChartOfAccount::exists()) {
            return;
        }

        $accounts = [
            ['code' => '1100', 'name' => 'Bank — Operating', 'account_type' => AccountType::Asset],
            ['code' => '1200', 'name' => 'Accounts Receivable — Fleet', 'account_type' => AccountType::Asset],
            ['code' => '1300', 'name' => 'Inventory', 'account_type' => AccountType::Asset],
            ['code' => '1400', 'name' => 'GRNI / Goods Received Not Invoiced', 'account_type' => AccountType::Asset],
            ['code' => '2100', 'name' => 'Accounts Payable — Suppliers', 'account_type' => AccountType::Liability],
            ['code' => '2200', 'name' => 'VAT Payable', 'account_type' => AccountType::Liability],
            ['code' => '2310', 'name' => 'PAYE Payable', 'account_type' => AccountType::Liability],
            ['code' => '2320', 'name' => 'NSSF Payable — Employee', 'account_type' => AccountType::Liability],
            ['code' => '2321', 'name' => 'NSSF Payable — Employer', 'account_type' => AccountType::Liability],
            ['code' => '2330', 'name' => 'SHIF Payable', 'account_type' => AccountType::Liability],
            ['code' => '2340', 'name' => 'Housing Levy Payable', 'account_type' => AccountType::Liability],
            ['code' => '2400', 'name' => 'Net Wages Payable', 'account_type' => AccountType::Liability],
            ['code' => '3000', 'name' => 'Owner\'s Equity', 'account_type' => AccountType::Equity],
            ['code' => '4000', 'name' => 'Sales Revenue — Parts', 'account_type' => AccountType::Revenue],
            ['code' => '4900', 'name' => 'Sales Returns & Allowances', 'account_type' => AccountType::Revenue],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'account_type' => AccountType::Expense],
            ['code' => '6100', 'name' => 'Salaries & Wages', 'account_type' => AccountType::Expense],
            ['code' => '6200', 'name' => 'Employer Statutory Contributions', 'account_type' => AccountType::Expense],
            ['code' => '6300', 'name' => 'Inventory Shrinkage', 'account_type' => AccountType::Expense],
        ];

        foreach ($accounts as $row) {
            ChartOfAccount::create([
                'code' => $row['code'],
                'name' => $row['name'],
                'account_type' => $row['account_type'],
                'normal_balance' => $row['account_type']->normalBalance(),
                'is_system' => true,
                'is_active' => true,
            ]);
        }

        $prefix = config('finance.cash_account_prefix', '1110');

        foreach (Shop::all() as $shop) {
            foreach (Payment::methods() as $method => $label) {
                ChartOfAccount::create([
                    'code' => $prefix.'-'.$shop->code.'-'.strtoupper($method),
                    'name' => "Cash — {$shop->name} — {$label}",
                    'account_type' => AccountType::Asset,
                    'normal_balance' => NormalBalance::Debit,
                    'shop_id' => $shop->id,
                    'payment_method' => $method,
                    'is_system' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
