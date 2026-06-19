<?php

namespace Database\Seeders;

use App\Models\ProcurementFolder;
use App\Models\ProcurementItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CostAnalysisService;
use Illuminate\Database\Seeder;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daybyday.test')->first();
        $supplier = Supplier::query()->orderBy('id')->first();
        $products = Product::active()->limit(3)->get();

        if (! $admin || ! $supplier || $products->isEmpty()) {
            return;
        }

        $draft = ProcurementFolder::firstOrCreate(
            ['folder_number' => 'PF-'.date('Y').'-9001'],
            [
                'supplier_id' => $supplier->id,
                'currency' => 'KES',
                'exchange_rate' => 1,
                'import_type' => 'Sea freight',
                'status' => 'draft',
                'notes' => 'Demo draft folder — add lines, run cost analysis, then submit for approval.',
                'total_freight' => 15000,
                'total_tax' => 8500,
                'created_by' => $admin->id,
            ]
        );

        if ($draft->items()->doesntExist()) {
            foreach ($products as $index => $product) {
                ProcurementItem::create([
                    'procurement_folder_id' => $draft->id,
                    'product_id' => $product->id,
                    'quantity' => 20 - ($index * 5),
                    'unit_cost' => (float) $product->cost_price,
                    'cbm' => 0.5 - ($index * 0.1),
                ]);
            }
        }

        $ready = ProcurementFolder::firstOrCreate(
            ['folder_number' => 'PF-'.date('Y').'-9002'],
            [
                'supplier_id' => $supplier->id,
                'currency' => 'USD',
                'exchange_rate' => 130,
                'import_type' => 'Air freight',
                'status' => 'draft',
                'notes' => 'Ready for approval after cost analysis — submit from the Workflow tab.',
                'total_freight' => 2500,
                'total_tax' => 1200,
                'created_by' => $admin->id,
            ]
        );

        if ($ready->status === 'draft' && $ready->items()->doesntExist()) {
            foreach ($products->take(2) as $index => $product) {
                ProcurementItem::create([
                    'procurement_folder_id' => $ready->id,
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => (float) $product->cost_price * 0.85,
                    'cbm' => 0.3 + ($index * 0.15),
                ]);
            }

            app(CostAnalysisService::class)->analyze($ready, 2500, 1200, 35);
        }
    }
}
