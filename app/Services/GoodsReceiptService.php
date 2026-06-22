<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\QuotationSeries;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(
        private InventoryService $inventory,
        private ProcurementService $procurement,
    ) {}

    public function receive(
        PurchaseOrder $purchaseOrder,
        int $warehouseId,
        array $lines,
        ?string $notes = null,
        ?User $user = null
    ): GoodsReceiptNote {
        $user ??= auth()->user();
        $purchaseOrder->load(['items.product', 'quotationSeries', 'supplier']);

        if (! $purchaseOrder->canReceive()) {
            throw new InventoryException('This purchase order cannot receive goods.');
        }

        $lines = $this->normalizeReceiptLines($lines);

        if ($lines === []) {
            throw new InventoryException('At least one line with a received quantity greater than zero is required.');
        }

        return DB::transaction(function () use ($purchaseOrder, $warehouseId, $lines, $notes, $user) {
            $warehouse = \App\Models\Warehouse::findOrFail($warehouseId);

            $grn = GoodsReceiptNote::create([
                'grn_number' => GoodsReceiptNote::generateNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'quotation_series_id' => $purchaseOrder->quotation_series_id,
                'warehouse_id' => $warehouse->id,
                'received_by' => $user->id,
                'received_at' => now(),
                'notes' => $notes,
                'status' => 'posted',
            ]);

            foreach ($lines as $line) {
                $this->processReceiptLine($purchaseOrder, $grn, $warehouse, $line, $user);
            }

            $this->syncPurchaseOrderReceiptState($purchaseOrder);

            return $grn->load(['items.product', 'warehouse', 'purchaseOrder']);
        });
    }

    public function closePurchaseOrderShort(PurchaseOrder $purchaseOrder, string $reason, ?User $user = null): PurchaseOrder
    {
        $user ??= auth()->user();
        $purchaseOrder->load('items');

        if (! $purchaseOrder->canCloseShort()) {
            throw new InventoryException('This purchase order cannot be closed short.');
        }

        return DB::transaction(function () use ($purchaseOrder, $reason, $user) {
            $purchaseOrder->update([
                'status' => 'closed_short',
                'delivery_status' => 'delivered',
                'closed_short_at' => now(),
                'closed_short_by' => $user->id,
                'close_short_reason' => $reason,
            ]);

            $this->syncQuotationSeriesReceiptState($purchaseOrder->quotationSeries);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'quotationSeries', 'closedShortBy']);
        });
    }

    public function void(GoodsReceiptNote $grn, string $reason, ?User $user = null): GoodsReceiptNote
    {
        $user ??= auth()->user();
        $grn->load(['items.product', 'warehouse', 'purchaseOrder.items']);

        if (! $grn->canVoid()) {
            throw new InventoryException('This goods receipt cannot be voided.');
        }

        return DB::transaction(function () use ($grn, $reason, $user) {
            $purchaseOrder = $grn->purchaseOrder;

            foreach ($grn->items as $item) {
                $goodQty = $item->goodQuantity();

                if ($goodQty > 0 && $grn->warehouse) {
                    $this->inventory->record(
                        $item->product,
                        $grn->warehouse,
                        'purchase_receipt_void',
                        -$goodQty,
                        (float) $item->unit_cost,
                        $grn,
                        $grn->grn_number,
                        'GRN void'.($reason ? ": {$reason}" : ''),
                        $user
                    );

                    $this->inventory->syncProductCostFromStock($item->product);
                }

                if ($purchaseOrder) {
                    $poItem = $purchaseOrder->items->firstWhere('product_id', $item->product_id);

                    if ($poItem) {
                        $poItem->received_quantity = max(
                            0,
                            round((float) $poItem->received_quantity - (float) $item->received_quantity, 2)
                        );
                        $poItem->save();
                    }
                }
            }

            $grn->update([
                'status' => 'voided',
                'voided_by' => $user->id,
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);

            if ($purchaseOrder) {
                $this->syncPurchaseOrderReceiptState($purchaseOrder);
            }

            return $grn->fresh(['items.product', 'warehouse', 'purchaseOrder', 'voidedBy']);
        });
    }

    public function syncPurchaseOrderReceiptState(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->load('items');

        if ($purchaseOrder->status === 'closed_short') {
            $anyReceived = $purchaseOrder->items->contains(
                fn ($item) => (float) $item->received_quantity > 0
            );

            $purchaseOrder->update([
                'status' => $anyReceived ? 'partially_received' : 'sent',
                'closed_short_at' => null,
                'closed_short_by' => null,
                'close_short_reason' => null,
            ]);

            $this->syncQuotationSeriesReceiptState($purchaseOrder->quotationSeries);

            return;
        }

        $allReceived = $purchaseOrder->items->every(
            fn ($item) => (float) $item->received_quantity >= (float) $item->quantity
        );
        $anyReceived = $purchaseOrder->items->contains(
            fn ($item) => (float) $item->received_quantity > 0
        );

        if ($allReceived) {
            $status = 'received';
            $deliveryStatus = 'delivered';
        } elseif ($anyReceived) {
            $status = 'partially_received';
            $deliveryStatus = $purchaseOrder->delivery_status;
        } else {
            $status = 'sent';
            $deliveryStatus = 'pending';
        }

        $purchaseOrder->update([
            'status' => $status,
            'delivery_status' => $deliveryStatus,
        ]);

        $this->syncQuotationSeriesReceiptState($purchaseOrder->quotationSeries);
    }

    public function reconcilePurchaseOrderReceiptState(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->receiptStateIsStale()) {
            $this->syncPurchaseOrderReceiptState($purchaseOrder);

            return $purchaseOrder->fresh(['items', 'supplier', 'quotationSeries']);
        }

        return $purchaseOrder;
    }

    public function syncQuotationSeriesReceiptState(?QuotationSeries $series): void
    {
        if (! $series) {
            return;
        }

        $purchaseOrders = $series->purchaseOrders()->with('items')->get();

        if ($purchaseOrders->isEmpty()) {
            return;
        }

        $allComplete = $purchaseOrders->every(
            fn (PurchaseOrder $po) => $po->isReceiptComplete()
        );

        if ($allComplete && ! in_array($series->status, ['closed', 'cancelled'], true)) {
            if ($series->status !== 'received') {
                $series->update(['status' => 'received']);
                $series->refresh();
            }

            $this->procurement->closeSeries($series);

            return;
        }

        if (! $allComplete && in_array($series->status, ['closed', 'received'], true)) {
            $this->reopenQuotationSeriesAfterReceiptChange($series, $purchaseOrders);
        }
    }

    private function reopenQuotationSeriesAfterReceiptChange(QuotationSeries $series, $purchaseOrders): void
    {
        $anyReceived = $purchaseOrders->contains(
            fn (PurchaseOrder $po) => $po->hasAnyReceipt()
        );

        $anyInTransit = $purchaseOrders->contains(
            fn (PurchaseOrder $po) => $po->delivery_status === 'in_transit'
        );

        $newStatus = $anyReceived
            ? 'received'
            : ($anyInTransit ? 'in_transit' : 'po_generated');

        $title = $series->title;
        if ($title && str_contains($title, ' - COMPLETED-CLOSED')) {
            $title = str_replace(' - COMPLETED-CLOSED', '', $title);
        }

        $series->update([
            'status' => $newStatus,
            'closed_at' => null,
            'title' => $title,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeReceiptLines(array $lines): array
    {
        return collect($lines)
            ->filter(fn ($line) => ! empty($line['include']))
            ->map(function ($line) {
                return [
                    'product_id' => (int) $line['product_id'],
                    'received_quantity' => GoodsReceiptNoteItem::normalizeQuantity($line['received_quantity'] ?? 0),
                    'damaged_quantity' => GoodsReceiptNoteItem::normalizeQuantity($line['damaged_quantity'] ?? 0),
                    'unit_cost' => isset($line['unit_cost']) ? (float) $line['unit_cost'] : null,
                ];
            })
            ->filter(fn ($line) => $line['received_quantity'] > 0)
            ->values()
            ->all();
    }

    private function processReceiptLine(
        PurchaseOrder $purchaseOrder,
        GoodsReceiptNote $grn,
        \App\Models\Warehouse $warehouse,
        array $line,
        User $user
    ): void {
        $poItem = $purchaseOrder->items->firstWhere('product_id', $line['product_id'])
            ?? $purchaseOrder->items()->where('product_id', $line['product_id'])->firstOrFail();

        $received = $line['received_quantity'];
        $damaged = $line['damaged_quantity'];
        $goodQty = max(0, round($received - $damaged, 2));

        if ($damaged > $received) {
            throw new InventoryException(
                "Damaged quantity cannot exceed received for {$poItem->product->part_number}."
            );
        }

        if ($received > $poItem->remainingQuantity() + 0.001) {
            throw new InventoryException(
                "Received quantity exceeds remaining for {$poItem->product->part_number}."
            );
        }

        $unitCost = $line['unit_cost'] ?? (float) $poItem->unit_cost;

        GoodsReceiptNoteItem::create([
            'goods_receipt_note_id' => $grn->id,
            'product_id' => $poItem->product_id,
            'expected_quantity' => $poItem->remainingQuantity(),
            'received_quantity' => $received,
            'damaged_quantity' => $damaged,
            'unit_cost' => $unitCost,
        ]);

        if ($goodQty > 0) {
            $this->inventory->record(
                $poItem->product,
                $warehouse,
                'purchase_receipt',
                $goodQty,
                $unitCost,
                $grn,
                $grn->grn_number,
                'Goods receipt from PO '.$purchaseOrder->po_number,
                $user
            );

            $this->updateProductCost($poItem->product, $unitCost);
        }

        $poItem->received_quantity = round((float) $poItem->received_quantity + $received, 2);
        $poItem->save();
    }

    private function updateProductCost(Product $product, float $unitCost): void
    {
        $product->update(['cost_price' => $unitCost]);
    }
}
