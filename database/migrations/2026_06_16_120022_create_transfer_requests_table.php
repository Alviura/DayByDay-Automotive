<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A request to move stock. `source` and `destination` are polymorphic over
     * warehouses and shops so the same table covers warehouse-to-shop and
     * inter-shop requests.
     */
    public function up(): void
    {
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->enum('type', ['warehouse_to_shop', 'inter_shop']);
            $table->nullableMorphs('source');
            $table->nullableMorphs('destination');
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'returned',
                'dispatched',
                'completed',
                'cancelled',
            ])->default('draft');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};
