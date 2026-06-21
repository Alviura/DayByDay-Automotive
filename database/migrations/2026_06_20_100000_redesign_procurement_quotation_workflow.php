<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->enum('purchase_type', ['local', 'import'])->default('local')->after('currency');
        });

        DB::table('suppliers')->where('country', 'Kenya')->update(['purchase_type' => 'local']);
        DB::table('suppliers')->where('country', '!=', 'Kenya')->update(['purchase_type' => 'import']);

        DB::statement("ALTER TABLE procurement_folders MODIFY status VARCHAR(50) NOT NULL DEFAULT 'quotation_draft'");

        Schema::table('procurement_folders', function (Blueprint $table) {
            $table->string('title')->nullable()->after('folder_number');
            $table->string('description')->nullable()->after('title');
            $table->enum('purchase_type', ['local', 'import'])->default('local')->after('exchange_rate');
            $table->decimal('cbm_rate', 15, 2)->nullable()->after('purchase_type');
            $table->decimal('total_purchase_price', 18, 2)->default(0)->after('total_cost');
            $table->decimal('total_cbm', 12, 4)->nullable()->after('total_purchase_price');
            $table->decimal('total_transport_cost', 18, 2)->default(0)->after('total_cbm');
            $table->decimal('total_actual_cost', 18, 2)->default(0)->after('total_transport_cost');
            $table->decimal('total_expected_sales', 18, 2)->default(0)->after('total_actual_cost');
            $table->decimal('total_expected_margin', 18, 2)->default(0)->after('total_expected_sales');
        });

        DB::table('procurement_folders')->where('status', 'draft')->update(['status' => 'quotation_draft']);
        DB::table('procurement_folders')->where('status', 'cost_analysis')->update(['status' => 'order_draft']);
        DB::table('procurement_folders')->where('status', 'pending_approval')->update(['status' => 'order_draft']);

        DB::table('procurement_folders')->whereNotNull('import_type')->where('import_type', 'like', '%local%')
            ->update(['purchase_type' => 'local']);
        DB::table('procurement_folders')->whereNotNull('import_type')->where('import_type', 'not like', '%local%')
            ->update(['purchase_type' => 'import']);

        Schema::table('procurement_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 4)->nullable()->after('quantity');
            $table->decimal('unit_price_foreign', 15, 4)->nullable()->after('unit_price');
            $table->decimal('unit_price_ksh', 15, 2)->nullable()->after('unit_price_foreign');
            $table->decimal('transport', 15, 2)->default(0)->after('unit_price_ksh');
            $table->decimal('width', 10, 4)->nullable()->after('transport');
            $table->decimal('length', 10, 4)->nullable()->after('width');
            $table->decimal('height', 10, 4)->nullable()->after('length');
            $table->decimal('quantity_per_packet', 15, 2)->default(1)->after('height');
            $table->decimal('number_of_packets', 15, 2)->nullable()->after('quantity_per_packet');
            $table->decimal('cbm_per_packet', 12, 6)->nullable()->after('number_of_packets');
            $table->decimal('total_cbm', 12, 4)->nullable()->after('cbm_per_packet');
            $table->decimal('transport_per_unit', 15, 2)->nullable()->after('total_cbm');
            $table->decimal('unit_cost_arrival', 15, 2)->nullable()->after('transport_per_unit');
            $table->decimal('market_wholesale_price', 15, 2)->nullable()->after('unit_cost_arrival');
            $table->decimal('margin_amount', 15, 2)->nullable()->after('market_wholesale_price');
            $table->decimal('margin_percent', 7, 2)->nullable()->after('margin_amount');
            $table->decimal('total_purchase_price', 18, 2)->nullable()->after('margin_percent');
            $table->decimal('actual_total_cost', 18, 2)->nullable()->after('total_purchase_price');
            $table->decimal('expected_sales', 18, 2)->nullable()->after('actual_total_cost');
            $table->decimal('expected_margin', 18, 2)->nullable()->after('expected_sales');
        });

        DB::table('procurement_items')->where('unit_cost', '>', 0)->update([
            'unit_price' => DB::raw('unit_cost'),
        ]);
    }

    public function down(): void
    {
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price', 'unit_price_foreign', 'unit_price_ksh', 'transport',
                'width', 'length', 'height', 'quantity_per_packet', 'number_of_packets',
                'cbm_per_packet', 'total_cbm', 'transport_per_unit', 'unit_cost_arrival',
                'market_wholesale_price', 'margin_amount', 'margin_percent',
                'total_purchase_price', 'actual_total_cost', 'expected_sales', 'expected_margin',
            ]);
        });

        Schema::table('procurement_folders', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'description', 'purchase_type', 'cbm_rate',
                'total_purchase_price', 'total_cbm', 'total_transport_cost',
                'total_actual_cost', 'total_expected_sales', 'total_expected_margin',
            ]);
        });

        DB::statement("ALTER TABLE procurement_folders MODIFY status ENUM(
            'draft','cost_analysis','pending_approval','approved','po_generated',
            'in_transit','received','closed','cancelled'
        ) NOT NULL DEFAULT 'draft'");

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('purchase_type');
        });
    }
};
