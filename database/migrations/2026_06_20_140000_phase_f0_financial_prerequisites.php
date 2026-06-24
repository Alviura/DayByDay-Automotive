<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('goods_receipt_note_id')->nullable()->constrained('goods_receipt_notes')->nullOnDelete();
            $table->string('supplier_invoice_number')->nullable();
            $table->decimal('amount', 18, 2);
            $table->enum('method', ['cash', 'mpesa', 'bank_transfer', 'card']);
            $table->string('reference')->nullable();
            $table->timestamp('paid_at');
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['posted', 'voided'])->default('posted');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index(['goods_receipt_note_id', 'status']);
            $table->index('paid_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('sale_id')->constrained('shops')->nullOnDelete();
            $table->enum('direction', ['receipt', 'refund'])->default('receipt')->after('method');
            $table->foreignId('reverses_payment_id')->nullable()->after('direction')->constrained('payments')->nullOnDelete();
        });

        Schema::table('customer_invoice_payments', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('customer_invoice_id')->constrained('shops')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('ar_recognized_at')->nullable()->after('sold_at');
            $table->timestamp('ar_invoiced_at')->nullable()->after('ar_recognized_at');
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->timestamp('ar_consolidated_at')->nullable()->after('issued_at');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid'");
        }

        Schema::create('tax_remittances', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('tax_collected', 18, 2)->default(0);
            $table->decimal('amount_remitted', 18, 2)->default(0);
            $table->enum('status', ['open', 'filed', 'paid'])->default('open');
            $table->date('due_date')->nullable();
            $table->timestamp('filed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['period_year', 'period_month']);
        });

        DB::table('payments')
            ->whereNull('shop_id')
            ->orderBy('id')
            ->chunkById(100, function ($payments) {
                foreach ($payments as $payment) {
                    $shopId = DB::table('sales')->where('id', $payment->sale_id)->value('shop_id');
                    if ($shopId) {
                        DB::table('payments')->where('id', $payment->id)->update(['shop_id' => $shopId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_remittances');

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropColumn('ar_consolidated_at');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['ar_recognized_at', 'ar_invoiced_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY payment_status ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid'");
        }

        Schema::table('customer_invoice_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reverses_payment_id');
            $table->dropColumn('direction');
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::dropIfExists('supplier_payments');
    }
};
