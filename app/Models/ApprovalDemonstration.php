<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Models\Concerns\Approvable;
use Illuminate\Database\Eloquent\Model;

class ApprovalDemonstration extends Model implements ApprovableDocument
{
    use Approvable;

    protected $fillable = [
        'reference',
        'title',
        'description',
        'module_type',
        'workflow_status',
    ];

    public function approvalTitle(): string
    {
        return $this->title;
    }

    public function approvalSummary(): string
    {
        return $this->description ?? 'No additional details provided.';
    }

    public function approvalReference(): string
    {
        return $this->reference;
    }

    public function approvalModuleKey(): string
    {
        return $this->module_type;
    }

    public function resolveApprovalApprover(): ?User
    {
        return app(\App\Services\ApprovalService::class)->resolveDefaultApprover();
    }

    public function onApprovalApproved(Approval $approval): void
    {
        $this->update(['workflow_status' => 'approved']);
    }

    public function onApprovalRejected(Approval $approval): void
    {
        $this->update(['workflow_status' => 'rejected']);
    }

    public function onApprovalReturned(Approval $approval): void
    {
        $this->update(['workflow_status' => 'returned']);
    }
}
