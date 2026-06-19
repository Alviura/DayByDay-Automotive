<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\TransferRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(private InventoryService $inventory) {}

    public function reserveForRequest(TransferRequest $request): void
    {
        $request->load(['items.product', 'source']);

        if (! $request->source) {
            throw new InventoryException('Transfer source is missing.');
        }

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                $qty = $item->dispatchQuantity();

                if ($qty > 0) {
                    $this->inventory->reserve($item->product, $request->source, $qty);
                }
            }
        });
    }

    public function dispatch(TransferRequest $request, ?string $notes = null, ?User $user = null): StockTransfer
    {
        if (! $request->canDispatch()) {
            throw new \InvalidArgumentException('This transfer request cannot be dispatched.');
        }

        $user ??= auth()->user();
        $request->load(['items.product', 'source', 'destination']);

        return DB::transaction(function () use ($request, $notes, $user) {
            $transfer = StockTransfer::create([
                'transfer_number' => StockTransfer::generateNumber(),
                'transfer_request_id' => $request->id,
                'source_type' => $request->source_type,
                'source_id' => $request->source_id,
                'destination_type' => $request->destination_type,
                'destination_id' => $request->destination_id,
                'status' => 'in_transit',
                'dispatched_by' => $user->id,
                'dispatched_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($request->items as $item) {
                $qty = $item->dispatchQuantity();

                if ($qty <= 0) {
                    continue;
                }

                $balance = $this->inventory->getBalance($item->product, $request->source);
                $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? 0);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item->product_id,
                    'dispatched_quantity' => $qty,
                    'received_quantity' => 0,
                    'damaged_quantity' => 0,
                ]);

                $this->inventory->record(
                    $item->product,
                    $request->source,
                    'transfer_out',
                    -$qty,
                    $unitCost,
                    $transfer,
                    $transfer->transfer_number,
                    'Transfer out to '.$request->destinationLabel(),
                    $user
                );

                $this->inventory->release($item->product, $request->source, $qty);
            }

            $request->update(['status' => 'dispatched']);

            return $transfer->load(['items.product', 'source', 'destination', 'transferRequest']);
        });
    }

    public function receive(StockTransfer $transfer, array $lines, ?string $notes = null, ?User $user = null): StockTransfer
    {
        if (! $transfer->canReceive()) {
            throw new \InvalidArgumentException('This transfer cannot be received.');
        }

        $user ??= auth()->user();
        $transfer->load(['items.product', 'source', 'destination', 'transferRequest']);

        return DB::transaction(function () use ($transfer, $lines, $notes, $user) {
            foreach ($lines as $line) {
                $item = $transfer->items()->where('product_id', $line['product_id'])->firstOrFail();
                $received = (float) $line['received_quantity'];
                $damaged = (float) ($line['damaged_quantity'] ?? 0);
                $goodQty = max(0, $received - $damaged);

                if ($received > $item->remainingQuantity() + 0.001) {
                    throw new InventoryException(
                        "Received quantity exceeds remaining for {$item->product->part_number}."
                    );
                }

                if ($goodQty > 0) {
                    $unitCost = $this->dispatchUnitCost($transfer, $item->product);

                    $this->inventory->record(
                        $item->product,
                        $transfer->destination,
                        'transfer_in',
                        $goodQty,
                        $unitCost,
                        $transfer,
                        $transfer->transfer_number,
                        'Transfer in from '.$transfer->source?->name,
                        $user
                    );
                }

                $item->update([
                    'received_quantity' => (float) $item->received_quantity + $received,
                    'damaged_quantity' => (float) $item->damaged_quantity + $damaged,
                ]);
            }

            $transfer->load('items');
            $allReceived = $transfer->items->every(
                fn (StockTransferItem $item) => (float) $item->received_quantity >= (float) $item->dispatched_quantity
            );

            $transfer->update([
                'status' => $allReceived ? 'received' : 'in_transit',
                'received_by' => $allReceived ? $user->id : $transfer->received_by,
                'received_at' => $allReceived ? now() : $transfer->received_at,
                'notes' => $notes ?? $transfer->notes,
            ]);

            if ($allReceived && $transfer->transferRequest) {
                $transfer->transferRequest->update(['status' => 'completed']);
                $transfer->update(['status' => 'closed']);
            }

            return $transfer->fresh(['items.product', 'source', 'destination', 'transferRequest']);
        });
    }

    private function dispatchUnitCost(StockTransfer $transfer, Product $product): float
    {
        $entry = StockLedger::query()
            ->where('reference_type', $transfer->getMorphClass())
            ->where('reference_id', $transfer->id)
            ->where('product_id', $product->id)
            ->where('transaction_type', 'transfer_out')
            ->first();

        return (float) ($entry?->unit_cost ?? $product->cost_price ?? 0);
    }
}
