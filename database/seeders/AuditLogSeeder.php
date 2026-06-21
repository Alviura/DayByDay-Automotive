<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\QuotationSeries;
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

        $series = QuotationSeries::first();
        if ($series) {
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'updated',
                'module' => 'quotation-series',
                'auditable_type' => $series->getMorphClass(),
                'auditable_id' => $series->id,
                'reference_number' => $series->series_number,
                'old_values' => ['status' => 'draft'],
                'new_values' => ['status' => $series->status],
                'ip_address' => '127.0.0.1',
            ]);
        }
    }
}
