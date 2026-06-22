<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnRecord;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ReturnSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = Supplier::query()->orderBy('id')->first();
        $warehouse = Warehouse::where('code', 'WH-MAIN')->first();
        $product = Product::active()->first();

        if (! $supplier || ! $warehouse || ! $product) {
            return;
        }

        $pendingReturn = ReturnRecord::firstOrCreate(
            ['return_number' => 'SRT-'.date('Y').'-9001'],
            [
                'type' => 'supplier',
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'reason' => 'Defective batch — demo supplier return awaiting approval.',
                'status' => 'pending',
            ]
        );

        $draftReturn = ReturnRecord::firstOrCreate(
            ['return_number' => 'SRT-'.date('Y').'-9000'],
            [
                'type' => 'supplier',
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'reason' => 'Draft supplier return — not yet submitted.',
                'status' => 'draft',
            ]
        );

        foreach ([$pendingReturn, $draftReturn] as $supplierReturn) {
            if ($supplierReturn->items()->doesntExist()) {
                ReturnItem::create([
                    'return_id' => $supplierReturn->id,
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'condition' => 'damaged',
                    'restock' => false,
                ]);
            }
        }
    }
}
