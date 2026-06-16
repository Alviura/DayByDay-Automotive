<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic approval engine. `approvable` polymorphically links to any document
     * needing approval (procurement folders, transfer requests, returns, stock
     * adjustments, large discounts, etc.), so approval logic lives in one place.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
