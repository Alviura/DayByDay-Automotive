<?php

namespace App\Notifications;

use App\Models\QuotationSeries;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProcurementClosedNotification extends Notification
{
    use Queueable;

    public function __construct(public QuotationSeries $series) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $series = $this->series->loadMissing('supplier');

        return [
            'type' => 'procurement',
            'icon' => 'fa-folder-closed',
            'title' => 'Procurement series closed',
            'message' => "{$series->series_number} with {$series->supplier?->name} is complete.",
            'module' => 'Quotation Series',
            'reference' => $series->series_number,
            'url' => route('quotation-series.show', $series),
        ];
    }
}
