<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\QuotationSeries;
use App\Models\User;
use App\Notifications\ProcurementClosedNotification;
use Illuminate\Support\Facades\DB;

class ProcurementService
{
    public function __construct(private NotificationRecipientService $notificationRecipients) {}
    public function generatePurchaseOrder(QuotationSeries $series, ?User $user = null): PurchaseOrder
    {
        if (! $series->canGeneratePo()) {
            throw new \InvalidArgumentException('This quotation series cannot generate a purchase order.');
        }

        $series->load('items.product');
        $user ??= auth()->user();

        return DB::transaction(function () use ($series, $user) {
            $po = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generateNumber(),
                'quotation_series_id' => $series->id,
                'supplier_id' => $series->supplier_id,
                'status' => 'sent',
                'delivery_status' => 'pending',
                'order_date' => now()->toDateString(),
                'expected_date' => now()->addDays($series->supplier?->lead_time_days ?? 14)->toDateString(),
                'currency' => $series->currency,
                'total' => $series->total_actual_cost ?: $series->total_landing_cost,
                'notes' => $series->notes,
                'created_by' => $user->id,
            ]);

            foreach ($series->items as $item) {
                $unitCost = $item->landedUnitCost() ?: (float) $item->unit_price;

                $po->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $unitCost,
                    'received_quantity' => 0,
                    'line_total' => (float) $item->quantity * $unitCost,
                ]);
            }

            $series->update(['status' => 'po_generated']);

            return $po->load(['items.product', 'supplier', 'quotationSeries']);
        });
    }

    public function markInTransit(QuotationSeries $series): void
    {
        if ($series->status !== 'po_generated' || ! $series->purchaseOrders()->exists()) {
            throw new \InvalidArgumentException('Generate a purchase order before marking in transit.');
        }

        $series->purchaseOrders()->update(['delivery_status' => 'in_transit', 'status' => 'sent']);
        $series->update(['status' => 'in_transit']);
    }

    public function closeSeries(QuotationSeries $series): void
    {
        if ($series->status !== 'received') {
            throw new \InvalidArgumentException('Quotation series can only be closed after all purchase orders have been received.');
        }

        $title = $series->title;
        if ($title && ! str_contains($title, 'COMPLETED-CLOSED')) {
            $title .= ' - COMPLETED-CLOSED';
        }

        $series->update([
            'status' => 'closed',
            'closed_at' => now(),
            'title' => $title,
        ]);

        $series->refresh();
        $this->notificationRecipients->notifyMany(
            $this->notificationRecipients->procurementStakeholders(),
            new ProcurementClosedNotification($series)
        );
    }
}
