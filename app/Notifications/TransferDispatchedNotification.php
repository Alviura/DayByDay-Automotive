<?php

namespace App\Notifications;

use App\Models\StockTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransferDispatchedNotification extends Notification
{
    use Queueable;

    public function __construct(public StockTransfer $transfer) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $transfer = $this->transfer->loadMissing(['source', 'destination']);

        return [
            'type' => 'transfer',
            'icon' => 'fa-truck-fast',
            'title' => 'Transfer dispatched',
            'message' => "{$transfer->transfer_number} is in transit to {$transfer->destinationLabel()}.",
            'module' => 'Stock Transfer',
            'reference' => $transfer->transfer_number,
            'url' => route('stock-transfers.show', $transfer),
        ];
    }
}
