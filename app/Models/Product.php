<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'max_selling_price' => 'decimal:2',
        'reorder_level' => 'integer',
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
