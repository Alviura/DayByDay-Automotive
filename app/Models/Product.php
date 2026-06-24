<?php

namespace App\Models;

use App\Enums\SupplierSellAs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'part_number',
        'name',
        'product_name_id',
        'vehicle_make_id',
        'vehicle_model_id',
        'category_id',
        'unit_id',
        'cost_price',
        'min_selling_price',
        'max_selling_price',
        'reorder_level',
        'width',
        'length',
        'height',
        'quantity_per_packet',
        'supplier_sell_as',
        'units_per_supplier_unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'max_selling_price' => 'decimal:2',
        'reorder_level' => 'integer',
        'width' => 'decimal:4',
        'length' => 'decimal:4',
        'height' => 'decimal:4',
        'quantity_per_packet' => 'decimal:2',
        'supplier_sell_as' => SupplierSellAs::class,
        'units_per_supplier_unit' => 'decimal:2',
    ];

    public function productName(): BelongsTo
    {
        return $this->belongsTo(ProductName::class);
    }

    public function vehicleMake(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class);
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function fitmentModels(): BelongsToMany
    {
        return $this->belongsToMany(VehicleModel::class, 'product_vehicle_model');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('part_number', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhereHas('productName', fn ($pn) => $pn->where('name', 'like', "%{$term}%"));
        });
    }

    public function hasPackagingDimensions(): bool
    {
        return $this->width !== null
            && $this->length !== null
            && $this->height !== null
            && (float) $this->width > 0
            && (float) $this->length > 0
            && (float) $this->height > 0;
    }

    public function cbmPerPacket(): ?float
    {
        if (! $this->hasPackagingDimensions()) {
            return null;
        }

        return round((float) $this->width * (float) $this->length * (float) $this->height, 6);
    }

    /**
     * Stock pieces in one CBM/shipping packet for import freight.
     * Falls back to pieces per pair/set when the product packaging field was left at default.
     */
    public function defaultQuantityPerPacket(): float
    {
        $explicit = $this->quantity_per_packet !== null
            ? (float) $this->quantity_per_packet
            : null;

        if ($explicit !== null && $explicit > 1) {
            return max(0.01, $explicit);
        }

        if ($this->isBundledSupplierUnit() && $this->unitsPerSupplierUnit() > 1) {
            return $this->unitsPerSupplierUnit();
        }

        return max(0.01, $explicit ?? 1);
    }

    /**
     * Default packaging fields for import quotation lines.
     *
     * @return array{width: ?float, length: ?float, height: ?float, quantity_per_packet: float}
     */
    public function packagingDefaults(): array
    {
        return [
            'width' => $this->width !== null ? (float) $this->width : null,
            'length' => $this->length !== null ? (float) $this->length : null,
            'height' => $this->height !== null ? (float) $this->height : null,
            'quantity_per_packet' => $this->defaultQuantityPerPacket(),
        ];
    }

    public function resolvedSupplierSellAs(): SupplierSellAs
    {
        if ($this->supplier_sell_as && $this->supplier_sell_as !== SupplierSellAs::Piece) {
            return $this->supplier_sell_as;
        }

        return $this->unit?->supplierSellAs()
            ?? $this->supplier_sell_as
            ?? SupplierSellAs::Piece;
    }

    public function unitsPerSupplierUnit(): float
    {
        return max(1, (float) ($this->units_per_supplier_unit ?? 1));
    }

    public function isBundledSupplierUnit(): bool
    {
        return $this->resolvedSupplierSellAs() !== SupplierSellAs::Piece;
    }

    public function isSoldAsBundle(): bool
    {
        return $this->isBundledSupplierUnit();
    }

    public function supplierSellAsLabel(): string
    {
        return $this->resolvedSupplierSellAs()->label();
    }

    public function supplierQuantityLabel(): string
    {
        return $this->resolvedSupplierSellAs()->quantityLabel();
    }

    public function orderUnitLabel(): string
    {
        return $this->resolvedSupplierSellAs()->orderUnitLabel();
    }

    public function stockQuantityFromOrder(float $orderQuantity): float
    {
        return $this->stockQuantityFromSaleQuantity($orderQuantity);
    }

    public function stockQuantityFromSaleQuantity(float $saleQuantity): float
    {
        return round($saleQuantity * $this->unitsPerSupplierUnit(), 2);
    }

    public function orderQuantityFromStock(float $stockQuantity): float
    {
        return $this->saleQuantityFromStock($stockQuantity);
    }

    public function saleQuantityFromStock(float $stockQuantity): float
    {
        return round($stockQuantity / $this->unitsPerSupplierUnit(), 2);
    }

    public function maxSaleQuantityFromStock(float $stockQuantity): float
    {
        return floor($stockQuantity / $this->unitsPerSupplierUnit() * 100) / 100;
    }

    public function sellingPriceLabel(): string
    {
        $min = (float) $this->min_selling_price;
        $max = (float) $this->max_selling_price;

        if ($min <= 0 && $max <= 0) {
            return '—';
        }

        if ($min === $max) {
            return number_format($min, 2);
        }

        return number_format($min, 2).' – '.number_format($max, 2);
    }
}
