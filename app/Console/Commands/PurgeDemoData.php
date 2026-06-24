<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeDemoData extends Command
{
    protected $signature = 'db:purge-demo-data {--force : Skip confirmation}';

    protected $description = 'Remove demo/seeded data, keeping users, chart of accounts, units, and roles/permissions';

    /**
     * Tables whose rows are preserved.
     *
     * @var list<string>
     */
    private array $preservedTables = [
        'users',
        'chart_of_accounts',
        'units',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'migrations',
        'password_reset_tokens',
    ];

    /**
     * Child tables first, then parents (FK checks disabled as a safeguard).
     *
     * @var list<string>
     */
    private array $tablesToTruncate = [
        'approval_actions',
        'approvals',
        'approval_demonstrations',
        'audit_logs',
        'notifications',
        'payroll_lines',
        'payroll_runs',
        'payroll_periods',
        'employee_salaries',
        'employees',
        'bank_reconciliation_items',
        'bank_reconciliations',
        'financial_periods',
        'tax_remittances',
        'supplier_payments',
        'journal_lines',
        'journal_entries',
        'customer_invoice_payments',
        'customer_invoices',
        'customer_accounts',
        'return_items',
        'returns',
        'payments',
        'sale_items',
        'sales',
        'goods_receipt_note_items',
        'goods_receipt_notes',
        'purchase_order_items',
        'purchase_orders',
        'quotation_items',
        'quotation_series',
        'stock_transfer_items',
        'stock_transfers',
        'transfer_request_items',
        'transfer_requests',
        'stock_adjustment_items',
        'stock_adjustments',
        'stock_balances',
        'stock_ledger',
        'product_vehicle_model',
        'products',
        'product_names',
        'categories',
        'vehicle_models',
        'vehicle_makes',
        'suppliers',
        'shops',
        'warehouses',
        'user_logins',
        'personal_access_tokens',
        'failed_jobs',
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm(
            'Delete all operational/demo data except users, chart of accounts, and units?'
        )) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        DB::table('users')->update([
            'shop_id' => null,
            'warehouse_id' => null,
        ]);

        Schema::disableForeignKeyConstraints();

        $truncated = 0;

        foreach ($this->tablesToTruncate as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (in_array($table, $this->preservedTables, true)) {
                continue;
            }

            DB::table($table)->truncate();
            $truncated++;
            $this->line("  Truncated <info>{$table}</info>");
        }

        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info("Purged {$truncated} tables. Preserved: users, chart_of_accounts, units, roles/permissions.");

        return self::SUCCESS;
    }
}
