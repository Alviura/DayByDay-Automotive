<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\TransferRequest;
use App\Models\User;
use App\Notifications\TransferDispatchedNotification;
use App\Notifications\TransferReceivedNotification;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(
        private InventoryService $inventory,
        private GlPostingService $gl,
        private NotificationRecipientService $notificationRecipients,
    ) {}

    public function createFromTransferRequest(TransferRequest $request, User $user): StockTransfer
    {
        $request->load(['items.product', 'source', 'destination']);

        return DB::transaction(function () use ($request, $user) {
            $transfer = StockTransfer::create([
                'transfer_number' => StockTransfer::generateNumber(),
                'transfer_request_id' => $request->id,
                'type' => $request->type,
                'source_type' => $request->source_type,
                'source_id' => $request->source_id,
                'destination_type' => $request->destination_type,
                'destination_id' => $request->destination_id,
                'status' => 'draft',
                'created_by' => $user->id,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->requested_quantity,
                ]);
            }

            $request->update(['stock_transfer_id' => $transfer->id]);

            return $transfer->load(['items.product', 'source', 'destination', 'transferRequest']);
        });
    }

    public function reserveForTransfer(StockTransfer $transfer): void
    {
        $transfer->load(['items.product', 'source']);

        if (! $transfer->source) {
            throw new InventoryException('Transfer source is missing.');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $qty = $item->transferQuantity();

                if ($qty > 0) {
                    $this->inventory->reserve($item->product, $transfer->source, $qty);
                }
            }
        });
    }

    public function dispatch(StockTransfer $transfer, ?string $notes = null, ?User $user = null): StockTransfer
    {
        if (! $transfer->canDispatch()) {
            throw new \InvalidArgumentException('This stock transfer cannot be dispatched.');
        }

        $user ??= auth()->user();
        $transfer->load(['items.product', 'source', 'destination', 'transferRequest']);

        return DB::transaction(function () use ($transfer, $notes, $user) {
            foreach ($transfer->items as $item) {
                $qty = $item->transferQuantity();

                if ($qty <= 0) {
                    continue;
                }

                $balance = $this->inventory->getBalance($item->product, $transfer->source);
                $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? 0);

                $item->update([
                    'dispatched_quantity' => $qty,
                ]);

                $this->inventory->record(
                    $item->product,
                    $transfer->source,
                    'transfer_out',
                    -$qty,
                    $unitCost,
                    $transfer,
                    $transfer->transfer_number,
                    'Transfer out to '.$transfer->destinationLabel(),
                    $user
                );

                $this->inventory->release($item->product, $transfer->source, $qty);
            }

            $transfer->update([
                'status' => 'in_transit',
                'dispatched_by' => $user->id,
                'dispatched_at' => now(),
                'notes' => $notes ?? $transfer->notes,
            ]);

            $transfer = $transfer->fresh(['items.product', 'source', 'destination', 'transferRequest']);
            $this->notificationRecipients->notifyMany(
                $this->notificationRecipients->receiversForTransfer($transfer),
                new TransferDispatchedNotification($transfer)
            );

            return $transfer;
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
                'status' => $allReceived ? 'closed' : 'in_transit',
                'received_by' => $allReceived ? $user->id : $transfer->received_by,
                'received_at' => $allReceived ? now() : $transfer->received_at,
                'notes' => $notes ?? $transfer->notes,
            ]);

            if ($allReceived && $transfer->transferRequest) {
                $transfer->transferRequest->update(['status' => 'fulfilled']);
            }

            $transfer = $transfer->fresh(['items.product', 'source', 'destination', 'transferRequest']);

            if ($transfer->status === 'closed') {
                $this->gl->postTransferCompleted($transfer, $user);

                $this->notificationRecipients->notifyMany(
                    $this->notificationRecipients->sourceStakeholdersForTransfer($transfer),
                    new TransferReceivedNotification($transfer)
                );
            }

            return $transfer;
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
