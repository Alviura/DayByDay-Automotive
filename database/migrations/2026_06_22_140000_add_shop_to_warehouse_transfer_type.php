<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transfer_requests MODIFY COLUMN type ENUM('warehouse_to_shop', 'inter_shop', 'shop_to_warehouse') NOT NULL");
    }

    public function down(): void
    {
        DB::table('transfer_requests')->where('type', 'shop_to_warehouse')->delete();

        DB::statement("ALTER TABLE transfer_requests MODIFY COLUMN type ENUM('warehouse_to_shop', 'inter_shop') NOT NULL");
    }
};
