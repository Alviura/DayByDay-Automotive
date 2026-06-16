<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_note_id')->constrained('goods_receipt_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('expected_quantity', 15, 2)->default(0);
            $table->decimal('received_quantity', 15, 2)->default(0);
            $table->decimal('damaged_quantity', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_note_items');
    }
};
