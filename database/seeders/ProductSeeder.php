<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductName;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $oilFilter = ProductName::where('name', 'Oil Filter')->first();
        $brakePad = ProductName::where('name', 'Brake Pad')->first();
        $airFilter = ProductName::where('name', 'Air Filter')->first();

        $filters = Category::where('name', 'Filters')->first();
        $engine = Category::where('name', 'Engine Parts')->whereNull('parent_id')->first();

        $pcs = Unit::where('abbreviation', 'PCS')->first();
        $set = Unit::where('abbreviation', 'SET')->first();

        $agl = Supplier::where('code', 'SUP-AGL')->first();
        $nms = Supplier::where('code', 'SUP-NMS')->first();

        $toyota = VehicleMake::where('name', 'Toyota')->first();
        $corolla = $toyota ? VehicleModel::where('vehicle_make_id', $toyota->id)->where('name', 'Corolla')->first() : null;
        $hilux = $toyota ? VehicleModel::where('vehicle_make_id', $toyota->id)->where('name', 'Hilux')->first() : null;
        $rav4 = $toyota ? VehicleModel::where('vehicle_make_id', $toyota->id)->where('name', 'RAV4')->first() : null;

        $samples = [
            [
                'part_number' => 'OIL-FIL-TYT-001',
                'name' => 'Oil Filter — Toyota Corolla',
                'product_name_id' => $oilFilter?->id,
                'vehicle_make_id' => $toyota?->id,
                'vehicle_model_id' => $corolla?->id,
                'category_id' => $filters?->id ?? $engine?->id,
                'unit_id' => $pcs?->id,
                'supplier_id' => $agl?->id,
                'cost_price' => 450.00,
                'selling_price' => 750.00,
                'reorder_level' => 10,
                'barcode' => '8901234567001',
                'description' => 'Genuine-spec oil filter for Toyota Corolla.',
                'fitment' => [$rav4?->id],
            ],
            [
                'part_number' => 'BRK-PAD-TYT-001',
                'name' => 'Front Brake Pad Set — Toyota Hilux',
                'product_name_id' => $brakePad?->id,
                'vehicle_make_id' => $toyota?->id,
                'vehicle_model_id' => $hilux?->id,
                'category_id' => $engine?->id,
                'unit_id' => $set?->id,
                'supplier_id' => $nms?->id,
                'cost_price' => 3200.00,
                'selling_price' => 4800.00,
                'reorder_level' => 5,
                'barcode' => '8901234567002',
                'description' => 'Ceramic front brake pad set.',
                'fitment' => [],
            ],
            [
                'part_number' => 'AIR-FIL-UNI-001',
                'name' => 'Universal Air Filter — Panel Type',
                'product_name_id' => $airFilter?->id,
                'vehicle_make_id' => null,
                'vehicle_model_id' => null,
                'category_id' => $filters?->id ?? $engine?->id,
                'unit_id' => $pcs?->id,
                'supplier_id' => $agl?->id,
                'cost_price' => 280.00,
                'selling_price' => 450.00,
                'reorder_level' => 20,
                'barcode' => null,
                'description' => 'Universal panel air filter — trim to fit.',
                'fitment' => array_filter([$corolla?->id, $rav4?->id]),
            ],
        ];

        foreach ($samples as $data) {
            $fitment = array_filter($data['fitment'] ?? []);
            unset($data['fitment']);

            $product = Product::updateOrCreate(
                ['part_number' => $data['part_number']],
                array_merge($data, ['is_active' => true])
            );

            $fitment = array_filter(
                $fitment,
                fn ($id) => $id && (int) $id !== (int) $product->vehicle_model_id
            );

            $product->fitmentModels()->sync($fitment);
        }
    }
}
