<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only inventory ledger. Every stock movement in the system creates a
     * row here. Quantity is signed: positive for stock-in, negative for stock-out.
     * `balance_after` stores the running balance for the product at that location.
     * `reference` is a polymorphic link to the source document (sale, transfer,
     * GRN, adjustment, return, etc.). `location` is polymorphic over warehouses
     * and shops.
     */
    public function up(): void
    {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->morphs('location');
            $table->enum('transaction_type', [
                'opening_balance',
                'purchase_receipt',
                'transfer_out',
                'transfer_in',
                'sale',
                'customer_return',
                'supplier_return',
                'adjustment',
            ]);
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2);
            $table->nullableMorphs('reference');
            $table->string('reference_number')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'location_type', 'location_id'], 'stock_ledger_product_location_idx');
            $table->index('transaction_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
