<?php

namespace App\Notifications;

use App\Models\GoodsReceiptNote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GoodsArrivedNotification extends Notification
{
    use Queueable;

    public function __construct(public GoodsReceiptNote $grn) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $grn = $this->grn->loadMissing(['purchaseOrder', 'warehouse', 'quotationSeries']);

        return [
            'type' => 'procurement',
            'icon' => 'fa-truck-ramp-box',
            'title' => 'Goods received',
            'message' => "{$grn->grn_number} posted for PO {$grn->purchaseOrder?->po_number}.",
            'module' => 'Goods Receipt',
            'reference' => $grn->grn_number,
            'url' => route('goods-receipts.show', $grn),
        ];
    }
}
