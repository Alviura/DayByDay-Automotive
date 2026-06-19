<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(private InventoryService $inventory) {}

    public function receive(
        PurchaseOrder $purchaseOrder,
        int $warehouseId,
        array $lines,
        ?string $notes = null,
        ?User $user = null
    ): GoodsReceiptNote {
        $user ??= auth()->user();
        $purchaseOrder->load(['items.product', 'folder', 'supplier']);

        return DB::transaction(function () use ($purchaseOrder, $warehouseId, $lines, $notes, $user) {
            $warehouse = \App\Models\Warehouse::findOrFail($warehouseId);

            $grn = GoodsReceiptNote::create([
                'grn_number' => GoodsReceiptNote::generateNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'procurement_folder_id' => $purchaseOrder->procurement_folder_id,
                'warehouse_id' => $warehouse->id,
                'received_by' => $user->id,
                'received_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                $poItem = $purchaseOrder->items()->where('product_id', $line['product_id'])->firstOrFail();
                $received = (float) $line['received_quantity'];
                $damaged = (float) ($line['damaged_quantity'] ?? 0);
                $goodQty = max(0, $received - $damaged);

                if ($received > $poItem->remainingQuantity() + 0.001) {
                    throw new InventoryException(
                        "Received quantity exceeds remaining for {$poItem->product->part_number}."
                    );
                }

                $unitCost = (float) ($line['unit_cost'] ?? $poItem->unit_cost);

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

                $poItem->received_quantity = (float) $poItem->received_quantity + $received;
                $poItem->save();
            }

            $this->refreshPurchaseOrderStatus($purchaseOrder);
            $this->refreshFolderStatus($purchaseOrder);

            return $grn->load(['items.product', 'warehouse', 'purchaseOrder']);
        });
    }

    private function updateProductCost(Product $product, float $unitCost): void
    {
        $suggested = round($unitCost * 1.3, 2);

        $product->update([
            'cost_price' => $unitCost,
            'min_selling_price' => (float) $product->min_selling_price > 0
                ? $product->min_selling_price
                : $suggested,
            'max_selling_price' => (float) $product->max_selling_price > 0
                ? $product->max_selling_price
                : $suggested,
        ]);
    }

    private function refreshPurchaseOrderStatus(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->load('items');
        $allReceived = $purchaseOrder->items->every(
            fn ($item) => (float) $item->received_quantity >= (float) $item->quantity
        );
        $anyReceived = $purchaseOrder->items->contains(
            fn ($item) => (float) $item->received_quantity > 0
        );

        $purchaseOrder->update([
            'status' => $allReceived ? 'received' : ($anyReceived ? 'partially_received' : $purchaseOrder->status),
            'delivery_status' => $allReceived ? 'delivered' : $purchaseOrder->delivery_status,
        ]);
    }

    private function refreshFolderStatus(PurchaseOrder $purchaseOrder): void
    {
        $folder = $purchaseOrder->folder;

        if (! $folder) {
            return;
        }

        $allPosReceived = $folder->purchaseOrders()
            ->get()
            ->every(fn (PurchaseOrder $po) => $po->status === 'received');

        if ($allPosReceived) {
            $folder->update(['status' => 'received']);
        }
    }
}
