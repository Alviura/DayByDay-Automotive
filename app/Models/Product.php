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
        'supplier_id',
        'cost_price',
        'selling_price',
        'reorder_level',
        'barcode',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
                ->orWhere('barcode', 'like', "%{$term}%");
        });
    }
}
