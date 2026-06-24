<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('supplier_sell_as', 10)->default('piece')->after('quantity_per_packet');
            $table->decimal('units_per_supplier_unit', 10, 2)->default(1)->after('supplier_sell_as');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['supplier_sell_as', 'units_per_supplier_unit']);
        });
    }
};
