<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daybyday.test')->first();
        $shop = Shop::where('code', 'SH-DTOWN')->first();
        $products = Product::active()->limit(2)->get();

        if (! $admin || ! $shop || $products->isEmpty()) {
            return;
        }

        if (Sale::where('receipt_number', 'RCP-'.date('Y').'-9001')->exists()) {
            return;
        }

        $sale = app(SaleService::class)->hold(
            $shop,
            $products->map(fn ($p, $i) => [
                'product_id' => $p->id,
                'quantity' => 2 - $i,
                'unit_price' => (float) $p->min_selling_price,
                'discount' => 0,
            ])->all(),
            $admin,
            null,
            'Demo Customer',
            '0712345678',
            'Seeded held sale — resume in POS to complete checkout.'
        );

        $sale->update(['receipt_number' => 'RCP-'.date('Y').'-9001']);
    }
}
