<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
        'shop_id',
        'warehouse_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * The shop this user is assigned to (typically a Shop Manager).
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * The warehouse this user is assigned to.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Login history records for this user.
     */
    public function logins(): HasMany
    {
        return $this->hasMany(UserLogin::class);
    }

    /**
     * Scope a query to only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }

    public function roleName(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function rolePillClass(): string
    {
        return match ($this->roleName()) {
            'Administrator' => 'usr-role-admin',
            'Shop Manager' => 'usr-role-shop',
            'Warehouse Manager' => 'usr-role-warehouse',
            'Shop Attendant' => 'usr-role-attendant',
            default => 'usr-role-default',
        };
    }

    public function locationLabel(): ?string
    {
        return $this->shop?->name ?? $this->warehouse?->name;
    }

    public function locationType(): ?string
    {
        if ($this->shop_id) {
            return 'shop';
        }

        if ($this->warehouse_id) {
            return 'warehouse';
        }

        return null;
    }
}
