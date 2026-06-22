<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_ledger MODIFY COLUMN transaction_type ENUM(
            'opening_balance',
            'purchase_receipt',
            'purchase_receipt_void',
            'transfer_out',
            'transfer_in',
            'sale',
            'customer_return',
            'supplier_return',
            'adjustment'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::table('stock_ledger')
            ->where('transaction_type', 'purchase_receipt_void')
            ->update(['transaction_type' => 'purchase_receipt']);

        DB::statement("ALTER TABLE stock_ledger MODIFY COLUMN transaction_type ENUM(
            'opening_balance',
            'purchase_receipt',
            'transfer_out',
            'transfer_in',
            'sale',
            'customer_return',
            'supplier_return',
            'adjustment'
        ) NOT NULL");
    }
};
