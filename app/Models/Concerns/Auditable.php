<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model) {
            if ($model->shouldAudit()) {
                AuditLog::record($model, 'created', null, $model->auditSnapshot());
            }
        });

        static::updated(function (self $model) {
            if (! $model->shouldAudit() || ! $model->wasChanged()) {
                return;
            }

            $changes = $model->auditChanges();

            if ($changes['new'] === []) {
                return;
            }

            AuditLog::record($model, 'updated', $changes['old'], $changes['new']);
        });

        static::deleted(function (self $model) {
            if ($model->shouldAudit()) {
                AuditLog::record($model, 'deleted', $model->auditSnapshot(), null);
            }
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function shouldAudit(): bool
    {
        return true;
    }

    protected function auditableFields(): array
    {
        return array_diff($this->fillable ?? [], $this->hiddenFromAudit());
    }

    protected function hiddenFromAudit(): array
    {
        return ['password', 'remember_token'];
    }

    protected function auditSnapshot(): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [$field => $this->getAttribute($field)])
            ->all();
    }

    protected function auditChanges(): array
    {
        $fields = $this->auditableFields();
        $old = [];
        $new = [];

        foreach (array_keys($this->getChanges()) as $key) {
            if (! in_array($key, $fields, true)) {
                continue;
            }

            $old[$key] = $this->getOriginal($key);
            $new[$key] = $this->getAttribute($key);
        }

        return ['old' => $old, 'new' => $new];
    }
}
