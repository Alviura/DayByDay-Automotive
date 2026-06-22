<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('ordered_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->after('ordered_by')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('sold_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ordered_by');
            $table->dropConstrainedForeignId('completed_by');
            $table->dropColumn('submitted_at');
        });
    }
};
