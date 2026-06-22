<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'national_id',
        'kra_pin',
        'nssf_number',
        'shif_number',
        'phone',
        'email',
        'address',
        'job_title',
        'employment_type',
        'hire_date',
        'termination_date',
        'station_type',
        'shop_id',
        'warehouse_id',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function currentSalary(): HasOne
    {
        return $this->hasOne(EmployeeSalary::class)
            ->whereNull('effective_to')
            ->latest('effective_from');
    }

    public function payrollLines(): HasMany
    {
        return $this->hasMany(PayrollLine::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnPayroll($query)
    {
        return $query->active()->whereNull('termination_date');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('employee_number', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('job_title', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function stationLabel(): string
    {
        return match ($this->station_type) {
            'shop' => $this->shop?->name ?? 'Shop',
            'warehouse' => $this->warehouse?->name ?? 'Warehouse',
            'field' => 'Field / Mobile',
            default => 'Head Office',
        };
    }

    public function hasSystemAccess(): bool
    {
        return $this->user_id !== null;
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "EMP-{$year}-";
        $last = static::withTrashed()
            ->where('employee_number', 'like', "{$prefix}%")
            ->orderByDesc('employee_number')
            ->value('employee_number');

        $sequence = $last
            ? ((int) substr($last, strlen($prefix))) + 1
            : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
