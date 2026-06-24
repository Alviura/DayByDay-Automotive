<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('width', 10, 4)->nullable()->after('reorder_level');
            $table->decimal('length', 10, 4)->nullable()->after('width');
            $table->decimal('height', 10, 4)->nullable()->after('length');
            $table->decimal('quantity_per_packet', 10, 2)->default(1)->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['width', 'length', 'height', 'quantity_per_packet']);
        });
    }
};
