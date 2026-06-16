<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->enum('condition', ['good', 'damaged'])->default('good');
            $table->boolean('restock')->default(true);
            $table->boolean('replacement')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
