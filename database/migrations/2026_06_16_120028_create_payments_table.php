<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per tender. A "mixed" payment is simply multiple rows against the
     * same sale (e.g. part cash + part M-Pesa).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->enum('method', ['cash', 'mpesa', 'bank_transfer', 'card']);
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
