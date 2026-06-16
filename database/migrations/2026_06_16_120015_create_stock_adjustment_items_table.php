<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('system_quantity', 15, 2)->default(0);
            $table->decimal('counted_quantity', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
