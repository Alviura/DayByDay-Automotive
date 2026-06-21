<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationSeries;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Procurement\QuotationCalculationService;
use Illuminate\Database\Seeder;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daybyday.test')->first();
        $localSupplier = Supplier::where('country', 'Kenya')->first();
        $importSupplier = Supplier::where('country', '!=', 'Kenya')->first();
        $products = Product::active()->limit(4)->get();

        if (! $admin || ! $localSupplier || $products->isEmpty()) {
            return;
        }

        $quotation = QuotationSeries::firstOrCreate(
            ['series_number' => 'PF-'.date('Y').'-9001'],
            [
                'title' => QuotationSeries::generateTitle($localSupplier, 'DEMO LOCAL QUOTATION'),
                'description' => 'DEMO LOCAL QUOTATION',
                'supplier_id' => $localSupplier->id,
                'currency' => 'KES',
                'exchange_rate' => 1,
                'purchase_type' => 'local',
                'import_type' => 'local',
                'status' => 'quotation_draft',
                'notes' => 'Demo quotation draft — add products and proceed to order processing.',
                'created_by' => $admin->id,
            ]
        );

        if ($quotation->items()->doesntExist()) {
            foreach ($products->take(3) as $index => $product) {
                QuotationItem::create([
                    'quotation_series_id' => $quotation->id,
                    'product_id' => $product->id,
                    'quantity' => [22, 5, 3][$index] ?? 10,
                ]);
            }
        }

        if ($importSupplier) {
            $importSeries = QuotationSeries::firstOrCreate(
                ['series_number' => 'PF-'.date('Y').'-9002'],
                [
                    'title' => QuotationSeries::generateTitle($importSupplier, 'DEMO IMPORT ORDER'),
                    'description' => 'DEMO IMPORT ORDER',
                    'supplier_id' => $importSupplier->id,
                    'currency' => 'USD',
                    'exchange_rate' => 31.5,
                    'cbm_rate' => 55033,
                    'purchase_type' => 'import',
                    'import_type' => 'import',
                    'status' => 'order_draft',
                    'notes' => 'Demo import order — enter prices and calculate.',
                    'created_by' => $admin->id,
                ]
            );

            if ($importSeries->items()->doesntExist()) {
                $importSeries->items()->create([
                    'quotation_series_id' => $importSeries->id,
                    'product_id' => $products->first()->id,
                    'quantity' => 300,
                    'unit_price_foreign' => 16.5375,
                    'width' => 0.07,
                    'length' => 0.17,
                    'height' => 0.09,
                    'quantity_per_packet' => 1,
                    'number_of_packets' => 300,
                ]);

                app(QuotationCalculationService::class)->calculate($importSeries);
            }
        }
    }
}
