<?php

namespace App\Contracts;

use App\Models\Approval;
use App\Models\User;

interface ApprovableDocument
{
    public function approvalTitle(): string;

    public function approvalSummary(): string;

    public function approvalReference(): string;

    /** Config key under approvals.modules (e.g. procurement, transfer, adjustment). */
    public function approvalModuleKey(): string;

    public function resolveApprovalApprover(): ?User;

    public function onApprovalApproved(Approval $approval): void;

    public function onApprovalRejected(Approval $approval): void;

    public function onApprovalReturned(Approval $approval): void;
}
