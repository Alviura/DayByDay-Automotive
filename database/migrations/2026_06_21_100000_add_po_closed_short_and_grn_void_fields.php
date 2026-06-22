<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('closed_short_at')->nullable()->after('notes');
            $table->foreignId('closed_short_by')->nullable()->after('closed_short_at')->constrained('users')->nullOnDelete();
            $table->text('close_short_reason')->nullable()->after('closed_short_by');
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft','sent','partially_received','received','closed_short','cancelled') NOT NULL DEFAULT 'draft'");

        Schema::table('goods_receipt_notes', function (Blueprint $table) {
            $table->string('status', 20)->default('posted')->after('notes');
            $table->foreignId('voided_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable()->after('voided_by');
            $table->text('void_reason')->nullable()->after('voided_at');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['status', 'voided_at', 'void_reason']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_short_by');
            $table->dropColumn(['closed_short_at', 'close_short_reason']);
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft','sent','partially_received','received','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
