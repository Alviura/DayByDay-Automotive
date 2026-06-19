<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\ProcurementFolder;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        if (AuditLog::exists()) {
            return;
        }

        $admin = User::where('email', 'admin@daybyday.test')->first();

        if (! $admin) {
            return;
        }

        $adjustment = StockAdjustment::first();
        if ($adjustment) {
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'created',
                'module' => 'adjustment',
                'auditable_type' => $adjustment->getMorphClass(),
                'auditable_id' => $adjustment->id,
                'reference_number' => $adjustment->adjustment_number,
                'new_values' => ['status' => 'draft', 'reason' => $adjustment->reason],
                'ip_address' => '127.0.0.1',
            ]);
        }

        $folder = ProcurementFolder::first();
        if ($folder) {
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'updated',
                'module' => 'procurement',
                'auditable_type' => $folder->getMorphClass(),
                'auditable_id' => $folder->id,
                'reference_number' => $folder->folder_number,
                'old_values' => ['status' => 'draft'],
                'new_values' => ['status' => $folder->status],
                'ip_address' => '127.0.0.1',
            ]);
        }
    }
}
