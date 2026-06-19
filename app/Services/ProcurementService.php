<?php

namespace App\Services;

use App\Models\ProcurementFolder;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcurementService
{
    public function generatePurchaseOrder(ProcurementFolder $folder, ?User $user = null): PurchaseOrder
    {
        if (! $folder->canGeneratePo()) {
            throw new \InvalidArgumentException('This folder cannot generate a purchase order.');
        }

        $folder->load('items.product');
        $user ??= auth()->user();

        return DB::transaction(function () use ($folder, $user) {
            $po = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generateNumber(),
                'procurement_folder_id' => $folder->id,
                'supplier_id' => $folder->supplier_id,
                'status' => 'sent',
                'delivery_status' => 'pending',
                'order_date' => now()->toDateString(),
                'expected_date' => now()->addDays($folder->supplier?->lead_time_days ?? 14)->toDateString(),
                'currency' => $folder->currency,
                'total' => $folder->total_landing_cost,
                'notes' => $folder->notes,
                'created_by' => $user->id,
            ]);

            foreach ($folder->items as $item) {
                $po->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->cost_per_unit ?: $item->unit_cost,
                    'received_quantity' => 0,
                    'line_total' => $item->quantity * ($item->cost_per_unit ?: $item->unit_cost),
                ]);
            }

            $folder->update(['status' => 'po_generated']);

            return $po->load(['items.product', 'supplier', 'folder']);
        });
    }

    public function markInTransit(ProcurementFolder $folder): void
    {
        if (! in_array($folder->status, ['po_generated', 'approved'], true)) {
            throw new \InvalidArgumentException('Folder must have a PO before marking in transit.');
        }

        $folder->purchaseOrders()->update(['delivery_status' => 'in_transit', 'status' => 'sent']);
        $folder->update(['status' => 'in_transit']);
    }

    public function closeFolder(ProcurementFolder $folder): void
    {
        if (! in_array($folder->status, ['received', 'in_transit', 'po_generated'], true)) {
            throw new \InvalidArgumentException('Folder cannot be closed in its current state.');
        }

        $folder->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}
