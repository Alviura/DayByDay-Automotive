<?php

namespace App\Notifications;

use App\Models\TransferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransferRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public TransferRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->request->loadMissing(['source', 'destination']);

        return [
            'type' => 'transfer',
            'icon' => 'fa-inbox',
            'title' => 'Transfer request submitted',
            'message' => "{$request->request_number} needs review ({$request->source?->name} → {$request->destination?->name}).",
            'module' => 'Transfer Request',
            'reference' => $request->request_number,
            'url' => route('transfer-requests.show', $request),
        ];
    }
}
