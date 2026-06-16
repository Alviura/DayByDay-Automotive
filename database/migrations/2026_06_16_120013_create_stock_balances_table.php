<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Derived, fast-read snapshot of current stock per product per location.
     * Maintained by the application alongside stock_ledger writes.
     * `quantity_available` is a generated column (on_hand - reserved).
     */
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->morphs('location');
            $table->decimal('quantity_on_hand', 15, 2)->default(0);
            $table->decimal('quantity_reserved', 15, 2)->default(0);
            $table->decimal('quantity_available', 15, 2)
                ->storedAs('quantity_on_hand - quantity_reserved');
            $table->decimal('average_cost', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'location_type', 'location_id'], 'stock_balances_product_location_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
