<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The physical movement of stock created when a transfer request is
     * dispatched. Generates stock_ledger entries on dispatch (out) and receipt (in).
     */
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('transfer_request_id')->nullable()->constrained('transfer_requests')->nullOnDelete();
            $table->nullableMorphs('source');
            $table->nullableMorphs('destination');
            $table->enum('status', ['dispatched', 'in_transit', 'received', 'closed', 'cancelled'])->default('dispatched');
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
