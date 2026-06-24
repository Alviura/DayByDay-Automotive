<?php

namespace App\Services\Procurement;

use App\Models\Product;
use App\Models\QuotationItem;

class QuotationQuantityResolver
{
    public static function unitsPerSupplierUnit(?Product $product): float
    {
        return max(1, (float) ($product?->units_per_supplier_unit ?? 1));
    }

    public static function isBundledSupplierUnit(?Product $product): bool
    {
        return self::unitsPerSupplierUnit($product) > 1;
    }

    public static function orderQuantity(QuotationItem $item, ?Product $product = null): float
    {
        $product ??= $item->product;

        if ($item->order_quantity !== null) {
            return (float) $item->order_quantity;
        }

        $units = self::unitsPerSupplierUnit($product);

        return $units > 0 ? (float) $item->quantity / $units : (float) $item->quantity;
    }

    public static function stockQuantity(QuotationItem $item, ?Product $product = null): float
    {
        $product ??= $item->product;
        $units = self::unitsPerSupplierUnit($product);

        return round(self::orderQuantity($item, $product) * $units, 2);
    }

    public static function syncQuantities(QuotationItem $item, ?Product $product = null): QuotationItem
    {
        $product ??= $item->product;
        $item->order_quantity = self::orderQuantity($item, $product);
        $item->quantity = self::stockQuantity($item, $product);

        return $item;
    }

    /**
     * Supplier units contained in one CBM-measured packet.
     */
    public static function supplierUnitsPerCbmPacket(float $quantityPerPacket, ?Product $product): float
    {
        $units = self::unitsPerSupplierUnit($product);

        return max(0.01, $quantityPerPacket / $units);
    }
}
