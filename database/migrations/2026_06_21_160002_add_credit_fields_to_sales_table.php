<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('sale_type', ['retail', 'credit'])->default('retail')->after('user_id');
            $table->foreignId('customer_account_id')->nullable()->after('sale_type')->constrained('customer_accounts')->nullOnDelete();
            $table->string('vehicle_plate', 20)->nullable()->after('customer_account_id');
            $table->foreignId('customer_invoice_id')->nullable()->after('vehicle_plate')->constrained('customer_invoices')->nullOnDelete();

            $table->index(['sale_type', 'payment_status']);
            $table->index(['customer_account_id', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_account_id']);
            $table->dropForeign(['customer_invoice_id']);
            $table->dropColumn(['sale_type', 'customer_account_id', 'vehicle_plate', 'customer_invoice_id']);
        });
    }
};
