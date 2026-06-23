<?php

namespace App\Notifications;

use App\Enums\ApprovalActionType;
use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalDecidedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Approval $approval,
        public ApprovalActionType $decision
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval',
            'icon' => 'fa-clipboard-check',
            'approval_id' => $this->approval->id,
            'title' => 'Approval '.$this->decision->label(),
            'message' => $this->approval->documentTitle().' was '.$this->decision->label().'.',
            'module' => $this->approval->moduleLabel(),
            'reference' => $this->approval->documentReference(),
            'decision' => $this->decision->value,
            'url' => route('approvals.show', $this->approval),
        ];
    }
}
