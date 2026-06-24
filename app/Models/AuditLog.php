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
            'stock_transfer' => 'Stock Transfer',
            'transfer' => 'Stock Transfer',
            'transfer_request' => 'Transfer Request',
            'sale' => 'Sale',
            'return' => 'Return',
            'journal' => 'Journal Entry',
            'user' => 'User',
            'product' => 'Product',
        ];
    }

    public function actionIcon(): string
    {
        return match ($this->action) {
            'created' => 'fa-plus',
            'updated' => 'fa-pen',
            'deleted' => 'fa-trash',
            default => 'fa-circle-dot',
        };
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created' => 'aud-action aud-action-create',
            'updated' => 'aud-action aud-action-update',
            'deleted' => 'aud-action aud-action-delete',
            default => 'aud-action aud-action-default',
        };
    }

    public function moduleIcon(): string
    {
        return match ($this->module) {
            'adjustment' => 'fa-boxes-stacked',
            'quotation-series', 'procurement' => 'fa-file-invoice-dollar',
            'stock_transfer', 'transfer' => 'fa-right-left',
            'transfer_request' => 'fa-paper-plane',
            'sale' => 'fa-cash-register',
            'return' => 'fa-rotate-left',
            'journal' => 'fa-book',
            'user' => 'fa-user',
            'product' => 'fa-box',
            default => 'fa-shield-halved',
        };
    }

    public function moduleBadgeClass(): string
    {
        return match ($this->module) {
            'adjustment' => 'aud-module aud-module-amber',
            'quotation-series', 'procurement' => 'aud-module aud-module-blue',
            'stock_transfer', 'transfer', 'transfer_request' => 'aud-module aud-module-indigo',
            'sale' => 'aud-module aud-module-green',
            'return' => 'aud-module aud-module-rose',
            'journal' => 'aud-module aud-module-purple',
            'user' => 'aud-module aud-module-slate',
            'product' => 'aud-module aud-module-orange',
            default => 'aud-module aud-module-slate',
        };
    }

    public function actorInitials(): string
    {
        if (! $this->user) {
            return 'SY';
        }

        $parts = preg_split('/\s+/', trim($this->user->name)) ?: [];

        return strtoupper(
            count($parts) >= 2
                ? substr($parts[0], 0, 1).substr($parts[1], 0, 1)
                : substr($parts[0] ?? 'U', 0, 2)
        );
    }

    public function auditableUrl(): ?string
    {
        $model = $this->auditable;

        if (! $model) {
            return null;
        }

        try {
            return match ($this->module) {
                'sale' => route('sales.show', $model),
                'stock_transfer', 'transfer' => route('stock-transfers.show', $model),
                'transfer_request' => route('transfer-requests.show', $model),
                'quotation-series', 'procurement' => route('quotation-series.show', $model),
                'adjustment' => route('stock-adjustments.show', $model),
                'journal' => route('journal-entries.show', $model),
                'user' => route('users.show', $model),
                'product' => route('products.show', $model),
                'return' => $model instanceof ReturnRecord
                    ? ($model->type === 'supplier'
                        ? route('supplier-returns.show', $model)
                        : route('customer-returns.show', $model))
                    : null,
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    public function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $string = (string) $value;

        return $string === '' ? '—' : $string;
    }

    public function fieldLabel(string $field): string
    {
        return str($field)->replace('_', ' ')->title()->toString();
    }

    public function eventHeadline(): string
    {
        $ref = $this->reference_number ? " ({$this->reference_number})" : '';
        $actor = $this->user?->name ?? 'System';

        return match ($this->action) {
            'created' => "{$actor} created a {$this->moduleLabel()} record{$ref}",
            'updated' => "{$actor} updated {$this->moduleLabel()}{$ref}",
            'deleted' => "{$actor} deleted {$this->moduleLabel()}{$ref}",
            default => "{$actor} performed {$this->action} on {$this->moduleLabel()}{$ref}",
        };
    }

    public function actionHeroClass(): string
    {
        return match ($this->action) {
            'created' => 'aud-hero-create',
            'updated' => 'aud-hero-update',
            'deleted' => 'aud-hero-delete',
            default => 'aud-hero-default',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadValues(): array
    {
        if ($this->action === 'created' && is_array($this->new_values)) {
            return $this->new_values;
        }

        if ($this->action === 'deleted' && is_array($this->old_values)) {
            return $this->old_values;
        }

        return [];
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
