<?php

namespace App\Models;

use App\Enums\SupplierSellAs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'abbreviation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('abbreviation', 'like', "%{$term}%");
        });
    }

    public function supplierSellAs(): ?SupplierSellAs
    {
        $key = strtolower(trim($this->abbreviation ?: $this->name ?: ''));

        return match ($key) {
            'pr', 'pair' => SupplierSellAs::Pair,
            'set' => SupplierSellAs::Set,
            'pcs', 'pc', 'piece' => SupplierSellAs::Piece,
            default => null,
        };
    }
}
