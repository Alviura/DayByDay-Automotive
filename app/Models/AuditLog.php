<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'auditable_type',
        'auditable_id',
        'reference_number',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public static function modules(): array
    {
        return [
            'adjustment' => 'Stock Adjustment',
            'quotation-series' => 'Quotation Series',
            'procurement' => 'Quotation Series',
            'transfer' => 'Transfer',
            'sale' => 'Sale',
            'return' => 'Return',
            'user' => 'User',
            'product' => 'Product',
        ];
    }

    public static function record(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $module = null,
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module ?? (method_exists($model, 'auditModule') ? $model->auditModule() : class_basename($model)),
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'reference_number' => method_exists($model, 'auditReferenceNumber')
                ? $model->auditReferenceNumber()
                : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            default => ucfirst($this->action),
        };
    }

    public function moduleLabel(): string
    {
        return static::modules()[$this->module] ?? ucfirst($this->module ?? 'System');
    }

    public function changedFields(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        return collect($keys)->map(fn ($key) => [
            'field' => $key,
            'old' => $old[$key] ?? null,
            'new' => $new[$key] ?? null,
        ])->all();
    }
}
