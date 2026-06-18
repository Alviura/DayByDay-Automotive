<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sandbox documents for exercising the approval engine until operational
     * modules (procurement, transfers, adjustments, etc.) are built.
     */
    public function up(): void
    {
        Schema::create('approval_demonstrations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('module_type')->default('demonstration');
            $table->enum('workflow_status', ['draft', 'pending', 'approved', 'rejected', 'returned'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_demonstrations');
    }
};
