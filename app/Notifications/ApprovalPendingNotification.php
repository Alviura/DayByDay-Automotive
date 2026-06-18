<?php

namespace App\Notifications;

use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalPendingNotification extends Notification
{
    use Queueable;

    public function __construct(public Approval $approval) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'approval_id' => $this->approval->id,
            'title' => 'Approval required',
            'message' => $this->approval->documentTitle().' is awaiting your decision.',
            'module' => $this->approval->moduleLabel(),
            'reference' => $this->approval->documentReference(),
            'url' => route('approvals.show', $this->approval),
        ];
    }
}
