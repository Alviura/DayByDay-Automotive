<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'requested_by',
        'current_approver_id',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'completed_at' => 'datetime',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class)->orderBy('created_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', ApprovalStatus::Pending);
    }

    public function scopeForApprover($query, User $user)
    {
        return $query->where('current_approver_id', $user->id);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('notes', 'like', "%{$term}%")
                ->orWhereHas('requester', fn ($rq) => $rq->where('name', 'like', "%{$term}%"))
                ->orWhereHas('currentApprover', fn ($aq) => $aq->where('name', 'like', "%{$term}%"));
        });
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::Pending;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            ApprovalStatus::Approved,
            ApprovalStatus::Rejected,
        ], true);
    }

    public function moduleKey(): ?string
    {
        $approvable = $this->approvable;

        if ($approvable instanceof \App\Contracts\ApprovableDocument) {
            return $approvable->approvalModuleKey();
        }

        return null;
    }

    public function moduleLabel(): string
    {
        $key = $this->moduleKey();

        if ($key) {
            return config("approvals.modules.{$key}.label", ucfirst($key));
        }

        return class_basename($this->approvable_type);
    }

    public function moduleIcon(): string
    {
        $key = $this->moduleKey();

        if ($key) {
            return config("approvals.modules.{$key}.icon", 'fa-file-lines');
        }

        return 'fa-file-lines';
    }

    public function documentTitle(): string
    {
        $approvable = $this->approvable;

        if ($approvable instanceof \App\Contracts\ApprovableDocument) {
            return $approvable->approvalTitle();
        }

        return $this->moduleLabel().' #'.$this->approvable_id;
    }

    public function documentReference(): string
    {
        $approvable = $this->approvable;

        if ($approvable instanceof \App\Contracts\ApprovableDocument) {
            return $approvable->approvalReference();
        }

        return '#'.$this->id;
    }

    public function documentSummary(): string
    {
        $approvable = $this->approvable;

        if ($approvable instanceof \App\Contracts\ApprovableDocument) {
            return $approvable->approvalSummary();
        }

        return $this->notes ?? '';
    }
}
