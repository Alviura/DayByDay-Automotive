<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->foreignId('shop_id')->nullable()->after('last_login_at')
                ->constrained('shops')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->after('shop_id')
                ->constrained('warehouses')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropColumn(['phone', 'is_active', 'last_login_at', 'deleted_at']);
        });
    }
};
