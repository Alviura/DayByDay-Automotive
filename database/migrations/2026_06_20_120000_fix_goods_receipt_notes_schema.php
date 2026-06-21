<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipt_notes', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_notes', 'quotation_series_id')) {
                $table->dropForeign(['quotation_series_id']);
                $table->dropColumn('quotation_series_id');
            }

            if (! Schema::hasColumn('goods_receipt_notes', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->after('grn_number')
                    ->constrained('purchase_orders')->nullOnDelete();
            }

            if (! Schema::hasColumn('goods_receipt_notes', 'procurement_folder_id')) {
                $table->foreignId('procurement_folder_id')->nullable()->after('purchase_order_id')
                    ->constrained('procurement_folders')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_notes', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_notes', 'procurement_folder_id')) {
                $table->dropForeign(['procurement_folder_id']);
                $table->dropColumn('procurement_folder_id');
            }

            if (Schema::hasColumn('goods_receipt_notes', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }

            if (! Schema::hasColumn('goods_receipt_notes', 'quotation_series_id')) {
                $table->foreignId('quotation_series_id')->nullable()->after('grn_number')
                    ->constrained('quotation_series')->nullOnDelete();
            }
        });
    }
};
