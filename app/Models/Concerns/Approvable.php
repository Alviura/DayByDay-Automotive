<?php

namespace App\Models\Concerns;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalStatus;
use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Approvable
{
    public function approval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->latestOfMany();
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function requestApproval(?string $notes = null): Approval
    {
        $this->assertApprovableDocument();

        if ($this->hasOpenApproval()) {
            throw new \App\Exceptions\ApprovalException('This document already has a pending approval.');
        }

        return app(ApprovalService::class)->submit($this, auth()->user(), $notes);
    }

    public function isApproved(): bool
    {
        return $this->approval?->status === ApprovalStatus::Approved;
    }

    public function isApprovalPending(): bool
    {
        return $this->approval?->status === ApprovalStatus::Pending;
    }

    public function isApprovalRejected(): bool
    {
        return $this->approval?->status === ApprovalStatus::Rejected;
    }

    public function wasReturnedForRevision(): bool
    {
        return $this->approval?->status === ApprovalStatus::Returned;
    }

    public function hasOpenApproval(): bool
    {
        return $this->approvals()->where('status', ApprovalStatus::Pending)->exists();
    }

    protected function assertApprovableDocument(): void
    {
        if (! $this instanceof ApprovableDocument) {
            throw new \App\Exceptions\ApprovalException(
                class_basename($this).' must implement '.ApprovableDocument::class.' to use approval workflow.'
            );
        }
    }
}
