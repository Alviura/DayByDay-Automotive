<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_request_id')->constrained('transfer_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('requested_quantity', 15, 2)->default(0);
            $table->decimal('approved_quantity', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_request_items');
    }
};
