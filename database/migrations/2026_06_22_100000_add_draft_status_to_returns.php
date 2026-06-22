<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `returns` MODIFY `status` ENUM('draft','pending','approved','rejected','completed') NOT NULL DEFAULT 'draft'");

        DB::table('returns')
            ->where('status', 'pending')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('approvals')
                    ->whereColumn('approvals.approvable_id', 'returns.id')
                    ->where('approvals.approvable_type', \App\Models\ReturnRecord::class);
            })
            ->update(['status' => 'draft']);
    }

    public function down(): void
    {
        DB::table('returns')->where('status', 'draft')->update(['status' => 'pending']);

        DB::statement("ALTER TABLE `returns` MODIFY `status` ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending'");
    }
};
