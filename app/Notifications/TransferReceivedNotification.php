<?php

namespace App\Notifications;

use App\Models\StockTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransferReceivedNotification extends Notification
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
            'icon' => 'fa-circle-check',
            'title' => 'Transfer received',
            'message' => "{$transfer->transfer_number} was received at {$transfer->destinationLabel()}.",
            'module' => 'Stock Transfer',
            'reference' => $transfer->transfer_number,
            'url' => route('stock-transfers.show', $transfer),
        ];
    }
}
