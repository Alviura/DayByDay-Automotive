<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product, public int $onHand, public int $reorderLevel) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inventory',
            'icon' => 'fa-triangle-exclamation',
            'product_id' => $this->product->id,
            'title' => 'Low stock alert',
            'message' => "{$this->product->part_number} is at {$this->onHand} units (reorder {$this->reorderLevel}).",
            'module' => 'Inventory',
            'reference' => $this->product->part_number,
            'url' => route('inventory.show', $this->product),
        ];
    }
}
