<?php

namespace App\Services;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Enums\ApprovalStatus;
use App\Exceptions\ApprovalException;
use App\Models\Approval;
use App\Models\ApprovalAction;
use App\Models\User;
use App\Notifications\ApprovalDecidedNotification;
use App\Notifications\ApprovalPendingNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function submit(Model $document, User $requester, ?string $notes = null): Approval
    {
        if (! $document instanceof ApprovableDocument) {
            throw new ApprovalException('Document must implement '.ApprovableDocument::class.'.');
        }

        return DB::transaction(function () use ($document, $requester, $notes) {
            $approver = $document->resolveApprovalApprover() ?? $this->resolveDefaultApprover();

            if (! $approver) {
                throw new ApprovalException('No approver could be assigned for this request.');
            }

            $approval = $document->approvals()->create([
                'status' => ApprovalStatus::Pending,
                'requested_by' => $requester->id,
                'current_approver_id' => $approver->id,
                'notes' => $notes,
            ]);

            $this->recordAction(
                $approval,
                $requester,
                ApprovalActionType::Commented,
                'Submitted for approval'.($notes ? ': '.$notes : '')
            );

            $approver->notify(new ApprovalPendingNotification($approval));

            return $approval->fresh(['requester', 'currentApprover', 'approvable']);
        });
    }

    public function approve(Approval $approval, User $actor, ?string $comments = null): Approval
    {
        return $this->transition($approval, $actor, ApprovalStatus::Approved, ApprovalActionType::Approved, $comments);
    }

    public function reject(Approval $approval, User $actor, ?string $comments = null): Approval
    {
        return $this->transition($approval, $actor, ApprovalStatus::Rejected, ApprovalActionType::Rejected, $comments);
    }

    public function returnForRevision(Approval $approval, User $actor, ?string $comments = null): Approval
    {
        return $this->transition($approval, $actor, ApprovalStatus::Returned, ApprovalActionType::Returned, $comments);
    }

    public function canAct(Approval $approval, User $user): bool
    {
        if (! $approval->isPending()) {
            return false;
        }

        if ($approval->current_approver_id === $user->id) {
            return true;
        }

        return $user->hasRole(config('approvals.default_approver_role'));
    }

    public function pendingCountFor(User $user): int
    {
        return Approval::pending()
            ->when(
                ! $user->hasRole(config('approvals.default_approver_role')),
                fn ($q) => $q->forApprover($user)
            )
            ->count();
    }

    /**
     * @return array<string, int>
     */
    public function pendingCountByModule(?User $user = null): array
    {
        $query = Approval::query()->pending();

        if ($user && ! $user->hasRole(config('approvals.default_approver_role'))) {
            $query->forApprover($user);
        }

        $counts = [];

        foreach (config('approvals.module_models', []) as $key => $model) {
            if (! (config("approvals.modules.{$key}.pipeline") ?? true)) {
                continue;
            }

            $counts[$key] = (clone $query)->where('approvable_type', $model)->count();
        }

        return $counts;
    }

    public function resolveDefaultApprover(): ?User
    {
        return User::role(config('approvals.default_approver_role'))
            ->active()
            ->orderBy('id')
            ->first();
    }

    private function transition(
        Approval $approval,
        User $actor,
        ApprovalStatus $status,
        ApprovalActionType $actionType,
        ?string $comments = null
    ): Approval {
        if (! $this->canAct($approval, $actor)) {
            throw new ApprovalException('You are not allowed to act on this approval.');
        }

        if ($actionType !== ApprovalActionType::Approved && blank($comments)) {
            throw new ApprovalException('Comments are required when rejecting or returning a request.');
        }

        return DB::transaction(function () use ($approval, $actor, $status, $actionType, $comments) {
            $approval->update([
                'status' => $status,
                'current_approver_id' => $status === ApprovalStatus::Returned
                    ? $approval->requested_by
                    : null,
                'completed_at' => in_array($status, [ApprovalStatus::Approved, ApprovalStatus::Rejected], true)
                    ? now()
                    : null,
            ]);

            $this->recordAction($approval, $actor, $actionType, $comments);

            $approvable = $approval->approvable;

            if ($approvable instanceof ApprovableDocument) {
                match ($status) {
                    ApprovalStatus::Approved => $approvable->onApprovalApproved($approval),
                    ApprovalStatus::Rejected => $approvable->onApprovalRejected($approval),
                    ApprovalStatus::Returned => $approvable->onApprovalReturned($approval),
                    default => null,
                };
            }

            if ($approval->requester && $approval->requester->isNot($actor)) {
                $approval->requester->notify(new ApprovalDecidedNotification($approval, $actionType));
            }

            return $approval->fresh(['requester', 'currentApprover', 'approvable', 'actions.actor']);
        });
    }

    private function recordAction(
        Approval $approval,
        User $actor,
        ApprovalActionType $action,
        ?string $comments = null
    ): ApprovalAction {
        return $approval->actions()->create([
            'actor_id' => $actor->id,
            'action' => $action,
            'comments' => $comments,
        ]);
    }
}
