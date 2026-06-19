<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\TransferRequest;
use App\Models\TransferRequestItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class TransferSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daybyday.test')->first();
        $warehouse = Warehouse::where('code', 'WH-MAIN')->first();
        $shop = Shop::where('code', 'SH-DTOWN')->first();
        $products = Product::active()->limit(2)->get();

        if (! $admin || ! $warehouse || ! $shop || $products->isEmpty()) {
            return;
        }

        $draft = TransferRequest::firstOrCreate(
            ['request_number' => 'TR-'.date('Y').'-9001'],
            [
                'type' => 'warehouse_to_shop',
                'source_type' => $warehouse->getMorphClass(),
                'source_id' => $warehouse->id,
                'destination_type' => $shop->getMorphClass(),
                'destination_id' => $shop->id,
                'status' => 'draft',
                'notes' => 'Demo warehouse-to-shop transfer — submit for approval to test the flow.',
                'requested_by' => $admin->id,
            ]
        );

        if ($draft->items()->doesntExist()) {
            foreach ($products as $index => $product) {
                TransferRequestItem::create([
                    'transfer_request_id' => $draft->id,
                    'product_id' => $product->id,
                    'requested_quantity' => 5 - $index,
                ]);
            }
        }
    }
}
