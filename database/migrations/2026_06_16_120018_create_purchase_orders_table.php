<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('procurement_folder_id')->nullable()->constrained('procurement_folders')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('status', ['draft', 'sent', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->enum('delivery_status', ['pending', 'in_transit', 'delivered'])->default('pending');
            $table->date('order_date')->nullable();
            $table->date('expected_date')->nullable();
            $table->char('currency', 3)->default('KES');
            $table->decimal('total', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
