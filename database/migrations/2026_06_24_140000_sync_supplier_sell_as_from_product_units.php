<?php

use App\Enums\SupplierSellAs;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Product::query()
            ->with('unit')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $sellAs = $product->unit?->supplierSellAs();

                    if (! $sellAs) {
                        continue;
                    }

                    $updates = ['supplier_sell_as' => $sellAs->value];

                    if ((float) $product->units_per_supplier_unit <= 1 && $sellAs !== SupplierSellAs::Piece) {
                        $updates['units_per_supplier_unit'] = $sellAs->defaultUnitsPerUnit();
                    }

                    $product->update($updates);
                }
            });
    }

    public function down(): void
    {
        // Data backfill — no rollback.
    }
};
