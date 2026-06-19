<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_selling_price', 15, 2)->default(0)->after('cost_price');
            $table->decimal('max_selling_price', 15, 2)->default(0)->after('min_selling_price');
        });

        DB::table('products')->update([
            'min_selling_price' => DB::raw('selling_price'),
            'max_selling_price' => DB::raw('selling_price'),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['selling_price', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('selling_price', 15, 2)->default(0)->after('cost_price');
            $table->string('barcode')->nullable()->unique()->after('reorder_level');
        });

        DB::table('products')->update([
            'selling_price' => DB::raw('min_selling_price'),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['min_selling_price', 'max_selling_price']);
        });
    }
};
