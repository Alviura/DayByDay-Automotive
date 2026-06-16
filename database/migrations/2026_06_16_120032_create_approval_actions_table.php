<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail of individual actions taken against an approval, supporting
     * multi-step / multi-actor approval flows.
     */
    public function up(): void
    {
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id')->constrained('approvals')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['approved', 'rejected', 'returned', 'commented']);
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
    }
};
