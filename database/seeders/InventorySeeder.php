<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daybyday.test')->first();
        $warehouse = Warehouse::where('code', 'WH-MAIN')->first();
        $shop = Shop::where('code', 'SH-DTOWN')->first();
        $products = Product::limit(3)->get();

        if (! $admin || ! $warehouse || ! $shop || $products->isEmpty()) {
            return;
        }

        $inventory = app(InventoryService::class);

        foreach ($products as $index => $product) {
            if (! $inventory->getBalance($product, $warehouse)) {
                $inventory->openingBalance($product, $warehouse, 50 - ($index * 10), (float) $product->cost_price, 'Seeded opening balance', $admin);
            }

            if (! $inventory->getBalance($product, $shop)) {
                $inventory->openingBalance($product, $shop, 15 - ($index * 3), (float) $product->cost_price, 'Seeded shop stock', $admin);
            }
        }

        $draft = StockAdjustment::firstOrCreate(
            ['adjustment_number' => 'ADJ-'.date('Y').'-9001'],
            [
                'location_type' => $warehouse->getMorphClass(),
                'location_id' => $warehouse->id,
                'reason' => 'count_variance',
                'status' => 'draft',
                'notes' => 'Demo draft adjustment — edit and submit to test approval flow.',
                'created_by' => $admin->id,
            ]
        );

        if ($draft->items()->doesntExist()) {
            $product = $products->first();
            $balance = $inventory->getBalance($product, $warehouse);

            StockAdjustmentItem::create([
                'stock_adjustment_id' => $draft->id,
                'product_id' => $product->id,
                'system_quantity' => $balance?->quantity_on_hand ?? 0,
                'counted_quantity' => ($balance?->quantity_on_hand ?? 0) - 2,
                'difference' => -2,
                'unit_cost' => $balance?->average_cost ?? $product->cost_price,
            ]);
        }
    }
}
