<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_folder_id')->constrained('procurement_folders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('cbm', 12, 4)->nullable();
            $table->decimal('freight_charge', 15, 2)->default(0);
            $table->decimal('tax_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->decimal('landing_cost', 18, 2)->default(0);
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            $table->decimal('margin', 7, 2)->nullable();
            $table->decimal('recommended_selling_price', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_items');
    }
};
