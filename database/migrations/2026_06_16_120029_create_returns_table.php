<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Handles both customer returns (linked to a sale) and supplier returns
     * (linked to a supplier), distinguished by `type`.
     */
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->enum('type', ['customer', 'supplier']);
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->decimal('refund_amount', 18, 2)->default(0);
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
