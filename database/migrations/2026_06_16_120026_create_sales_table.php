<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->decimal('change_due', 15, 2)->default(0);
            $table->enum('status', ['held', 'completed', 'reversed'])->default('held');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['shop_id', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
