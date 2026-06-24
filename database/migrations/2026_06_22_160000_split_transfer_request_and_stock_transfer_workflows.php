<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transfer_requests', 'reviewed_by')) {
            Schema::table('transfer_requests', function (Blueprint $table) {
                $table->foreignId('reviewed_by')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                $table->text('review_notes')->nullable()->after('reviewed_at');
                $table->foreignId('stock_transfer_id')->nullable()->after('review_notes')->constrained('stock_transfers')->nullOnDelete();
            });
        }

        DB::statement("ALTER TABLE transfer_requests MODIFY COLUMN status ENUM(
            'draft',
            'pending',
            'approved',
            'rejected',
            'returned',
            'dispatched',
            'completed',
            'cancelled',
            'submitted',
            'accepted',
            'fulfilled'
        ) NOT NULL DEFAULT 'draft'");

        DB::table('transfer_requests')->where('status', 'pending')->update(['status' => 'submitted']);
        DB::table('transfer_requests')->where('status', 'approved')->update(['status' => 'accepted']);
        DB::table('transfer_requests')->whereIn('status', ['dispatched', 'completed'])->update(['status' => 'fulfilled']);
        DB::table('transfer_requests')->where('status', 'returned')->update(['status' => 'draft']);
        DB::table('transfer_requests')->where('type', 'shop_to_warehouse')->delete();

        DB::statement("ALTER TABLE transfer_requests MODIFY COLUMN status ENUM(
            'draft',
            'submitted',
            'accepted',
            'rejected',
            'fulfilled',
            'cancelled'
        ) NOT NULL DEFAULT 'draft'");

        DB::statement("ALTER TABLE transfer_requests MODIFY COLUMN type ENUM('warehouse_to_shop', 'inter_shop') NOT NULL");

        if (! Schema::hasColumn('stock_transfers', 'type')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->enum('type', ['warehouse_to_shop', 'inter_shop', 'shop_to_warehouse'])->nullable()->after('transfer_number');
                $table->foreignId('created_by')->nullable()->after('destination_id')->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            });
        }

        DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN status ENUM(
            'dispatched',
            'in_transit',
            'received',
            'closed',
            'cancelled',
            'draft',
            'pending',
            'approved',
            'rejected',
            'returned'
        ) NOT NULL DEFAULT 'draft'");

        if (! Schema::hasColumn('stock_transfer_items', 'quantity')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                $table->decimal('quantity', 15, 2)->default(0)->after('product_id');
            });

            DB::table('stock_transfer_items')->update([
                'quantity' => DB::raw('dispatched_quantity'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_transfer_items', 'quantity')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }

        if (Schema::hasColumn('stock_transfers', 'type')) {
            Schema::table('stock_transfers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
                $table->dropConstrainedForeignId('approved_by');
                $table->dropColumn(['type', 'approved_at']);
            });
        }

        DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN status ENUM(
            'dispatched', 'in_transit', 'received', 'closed', 'cancelled'
        ) NOT NULL DEFAULT 'dispatched'");

        if (Schema::hasColumn('transfer_requests', 'reviewed_by')) {
            Schema::table('transfer_requests', function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by');
                $table->dropConstrainedForeignId('stock_transfer_id');
                $table->dropColumn(['reviewed_at', 'review_notes']);
            });
        }

        DB::statement("ALTER TABLE transfer_requests MODIFY COLUMN status ENUM(
            'draft', 'pending', 'approved', 'rejected', 'returned', 'dispatched', 'completed', 'cancelled'
        ) NOT NULL DEFAULT 'draft'");
    }
};
