<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('order_quantity', 15, 2)->nullable()->after('quantity');
            $table->decimal('transport_per_packet', 15, 2)->nullable()->after('transport_per_unit');
        });

        DB::statement('
            UPDATE quotation_items qi
            INNER JOIN products p ON p.id = qi.product_id
            SET qi.order_quantity = qi.quantity / GREATEST(p.units_per_supplier_unit, 1)
            WHERE qi.order_quantity IS NULL
        ');

        DB::table('quotation_items')
            ->whereNull('order_quantity')
            ->update(['order_quantity' => DB::raw('quantity')]);
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['order_quantity', 'transport_per_packet']);
        });
    }
};
