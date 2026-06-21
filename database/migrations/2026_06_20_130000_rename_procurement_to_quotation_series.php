<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('approvals')
            ->whereIn('approvable_type', [
                'App\Models\QuotationSeries',
                'App\Models\ProcurementFolder',
            ])
            ->delete();

        if (Schema::hasTable('procurement_folders')) {
            Schema::disableForeignKeyConstraints();
            Schema::dropIfExists('quotation_items');
            Schema::dropIfExists('quotation_series');
            Schema::rename('procurement_folders', 'quotation_series');
            Schema::rename('procurement_items', 'quotation_items');
            Schema::enableForeignKeyConstraints();
        }

        if (Schema::hasColumn('quotation_series', 'folder_number')) {
            DB::statement('ALTER TABLE quotation_series CHANGE folder_number series_number VARCHAR(255) NOT NULL');
        }

        $this->renameForeignKeyColumn('quotation_items', 'procurement_folder_id', 'quotation_series_id', true);
        $this->renameForeignKeyColumn('purchase_orders', 'procurement_folder_id', 'quotation_series_id', false);
        $this->renameForeignKeyColumn('goods_receipt_notes', 'procurement_folder_id', 'quotation_series_id', false);
    }

    public function down(): void
    {
        $this->renameForeignKeyColumn('goods_receipt_notes', 'quotation_series_id', 'procurement_folder_id', false);
        $this->renameForeignKeyColumn('purchase_orders', 'quotation_series_id', 'procurement_folder_id', false);
        $this->renameForeignKeyColumn('quotation_items', 'quotation_series_id', 'procurement_folder_id', true);

        if (Schema::hasColumn('quotation_series', 'series_number')) {
            DB::statement('ALTER TABLE quotation_series CHANGE series_number folder_number VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('quotation_series') && ! Schema::hasTable('procurement_folders')) {
            Schema::rename('quotation_items', 'procurement_items');
            Schema::rename('quotation_series', 'procurement_folders');
        }
    }

    private function renameForeignKeyColumn(
        string $table,
        string $from,
        string $to,
        bool $cascadeOnDelete,
    ): void {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $from)) {
            return;
        }

        $this->dropForeignKeyIfExists($table, $from);

        $nullable = $table !== 'quotation_items';
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        DB::statement("ALTER TABLE {$table} CHANGE {$from} {$to} BIGINT UNSIGNED {$nullSql}");

        Schema::table($table, function (Blueprint $blueprint) use ($to, $cascadeOnDelete, $nullable) {
            $foreign = $blueprint->foreign($to)->references('id')->on('quotation_series');
            $cascadeOnDelete ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
        });
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $database = Schema::getConnection()->getDatabaseName();

        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if ($constraint) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint}");
        }
    }
};
