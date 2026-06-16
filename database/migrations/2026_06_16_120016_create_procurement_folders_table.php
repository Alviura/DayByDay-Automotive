<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_number')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->char('currency', 3)->default('KES');
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->string('import_type')->nullable();
            $table->enum('status', [
                'draft',
                'cost_analysis',
                'pending_approval',
                'approved',
                'po_generated',
                'in_transit',
                'received',
                'closed',
                'cancelled',
            ])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->decimal('total_freight', 18, 2)->default(0);
            $table->decimal('total_tax', 18, 2)->default(0);
            $table->decimal('total_landing_cost', 18, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_folders');
    }
};
