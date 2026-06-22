<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(private InventoryService $inventory) {}

    public function hold(
        Shop $shop,
        array $items,
        ?User $user = null,
        ?Sale $existing = null,
        ?string $customerName = null,
        ?string $customerPhone = null,
        ?string $notes = null,
        string $saleType = 'retail',
        ?int $customerAccountId = null,
        ?string $vehiclePlate = null
    ): Sale {
        $user ??= auth()->user();

        return DB::transaction(function () use ($shop, $items, $user, $existing, $customerName, $customerPhone, $notes, $saleType, $customerAccountId, $vehiclePlate) {
            if ($saleType === 'credit' && ! $customerAccountId) {
                throw new \InvalidArgumentException('A customer account is required for credit sales.');
            }

            if ($saleType === 'credit' && ! trim($vehiclePlate ?? '')) {
                throw new \InvalidArgumentException('Vehicle plate is required for fleet sales.');
            }

            if ($existing) {
                if (! $existing->isHeld()) {
                    throw new \InvalidArgumentException('Only held sales can be updated.');
                }
                $this->releaseReservations($existing, $shop);
                $sale = $existing;
            } else {
                $sale = Sale::create([
                    'receipt_number' => Sale::generateReceiptNumber(),
                    'shop_id' => $shop->id,
                    'user_id' => $user->id,
                    'ordered_by' => $user->id,
                    'sale_type' => $saleType,
                    'customer_account_id' => $saleType === 'credit' ? $customerAccountId : null,
                    'vehicle_plate' => $saleType === 'credit' ? $vehiclePlate : null,
                    'status' => 'held',
                    'payment_status' => 'unpaid',
                    'submitted_at' => now(),
                ]);
            }

            $account = $saleType === 'credit' ? \App\Models\CustomerAccount::findOrFail($customerAccountId) : null;

            $sale->update([
                'sale_type' => $saleType,
                'customer_account_id' => $account?->id,
                'vehicle_plate' => $saleType === 'credit' ? $vehiclePlate : null,
                'customer_name' => $customerName ?: $account?->contact_name,
                'customer_phone' => $customerPhone ?: $account?->phone,
                'notes' => $notes,
            ]);

            if ($existing && ! $user?->can('sales.create')) {
                $sale->update(['submitted_at' => now()]);
            }

            $sale->items()->delete();
            foreach ($items as $line) {
                $product = Product::findOrFail($line['product_id']);
                $unitPrice = $this->resolveUnitPrice($product, $line, $user, $saleType);
                $this->assertAvailable($product, $shop, (float) $line['quantity']);
                $this->assertUnitPriceInRange($product, $unitPrice);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'discount' => 0,
                ]);
            }

            $this->recalculateTotals($sale);

            if ($account && $account->credit_limit !== null) {
                $outstanding = $account->outstandingBalance() + (float) $sale->total;
                if ($outstanding > (float) $account->credit_limit) {
                    throw new \InvalidArgumentException("Credit limit exceeded for {$account->name}. Outstanding would be ".number_format($outstanding, 2).'.');
                }
            }

            $this->reserveItems($sale, $shop);

            return $sale->fresh(['items.product.unit', 'shop', 'customerAccount']);
        });
    }

    public function completeOnAccount(Sale $sale, ?User $user = null): Sale
    {
        if (! $sale->canComplete()) {
            throw new \InvalidArgumentException('This sale cannot be completed.');
        }

        if (! $sale->isCredit()) {
            throw new \InvalidArgumentException('Only credit sales can be issued on account.');
        }

        $user ??= auth()->user();
        $sale->load(['items.product', 'shop', 'customerAccount']);

        if ($sale->customerAccount && $sale->customerAccount->credit_limit !== null) {
            $outstanding = $sale->customerAccount->outstandingBalance() + (float) $sale->total;
            if ($outstanding > (float) $sale->customerAccount->credit_limit) {
                throw new \InvalidArgumentException('Credit limit exceeded for this account.');
            }
        }

        return DB::transaction(function () use ($sale, $user) {
            foreach ($sale->items as $item) {
                $balance = $this->inventory->getBalance($item->product, $sale->shop);
                $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? 0);

                $this->inventory->release($item->product, $sale->shop, (float) $item->quantity);

                $this->inventory->record(
                    $item->product,
                    $sale->shop,
                    'sale',
                    -(float) $item->quantity,
                    $unitCost,
                    $sale,
                    $sale->receipt_number,
                    'Credit sale — on account',
                    $user
                );
            }

            $sale->update([
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                'change_due' => 0,
                'sold_at' => now(),
                'completed_by' => $user->id,
            ]);

            return $sale->fresh(['items.product.unit', 'shop', 'customerAccount', 'cashier']);
        });
    }

    public function complete(Sale $sale, array $payments, ?User $user = null): Sale
    {
        if (! $sale->canComplete()) {
            throw new \InvalidArgumentException('This sale cannot be completed.');
        }

        if ($sale->isCredit()) {
            throw new \InvalidArgumentException('Credit sales must be issued on account. Use the Issue on Account action instead.');
        }

        $user ??= auth()->user();
        $sale->load(['items.product', 'shop']);

        $amountPaid = collect($payments)->sum(fn ($p) => (float) $p['amount']);

        if ($amountPaid < (float) $sale->total) {
            throw new \InvalidArgumentException('Payment amount is less than the sale total.');
        }

        return DB::transaction(function () use ($sale, $payments, $user, $amountPaid) {
            foreach ($sale->items as $item) {
                $balance = $this->inventory->getBalance($item->product, $sale->shop);
                $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? 0);

                $this->inventory->release($item->product, $sale->shop, (float) $item->quantity);

                $this->inventory->record(
                    $item->product,
                    $sale->shop,
                    'sale',
                    -(float) $item->quantity,
                    $unitCost,
                    $sale,
                    $sale->receipt_number,
                    'POS sale',
                    $user
                );
            }

            $sale->payments()->delete();

            foreach ($payments as $payment) {
                if ((float) $payment['amount'] <= 0) {
                    continue;
                }

                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'paid_at' => now(),
                    'received_by' => $user->id,
                ]);
            }

            $sale->update([
                'status' => 'completed',
                'payment_status' => 'paid',
                'amount_paid' => $amountPaid,
                'change_due' => max(0, $amountPaid - (float) $sale->total),
                'sold_at' => now(),
                'completed_by' => $user->id,
            ]);

            return $sale->fresh(['items.product.unit', 'payments', 'shop', 'cashier']);
        });
    }

    public function reverse(Sale $sale, ?User $user = null, ?string $reason = null): Sale
    {
        if (! $sale->canReverse()) {
            throw new \InvalidArgumentException('Only completed sales can be reversed.');
        }

        $user ??= auth()->user();
        $sale->load(['items.product', 'shop']);

        return DB::transaction(function () use ($sale, $user, $reason) {
            foreach ($sale->items as $item) {
                $balance = $this->inventory->getBalance($item->product, $sale->shop);
                $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? 0);

                $this->inventory->record(
                    $item->product,
                    $sale->shop,
                    'sale',
                    (float) $item->quantity,
                    $unitCost,
                    $sale,
                    $sale->receipt_number,
                    'Sale reversal'.($reason ? ": {$reason}" : ''),
                    $user
                );
            }

            $sale->update([
                'status' => 'reversed',
                'reversed_by' => $user->id,
                'reversed_at' => now(),
                'notes' => trim(($sale->notes ?? '')."\nReversed: ".($reason ?? 'No reason given')),
            ]);

            return $sale->fresh(['items.product', 'payments', 'shop', 'reverser']);
        });
    }

    public function abandonHeld(Sale $sale): void
    {
        if (! $sale->isHeld()) {
            throw new \InvalidArgumentException('Only held sales can be abandoned.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items.product', 'shop');
            $this->releaseReservations($sale, $sale->shop);
            $sale->delete();
        });
    }

    public function recalculateTotals(Sale $sale): Sale
    {
        $sale->load('items');

        $subtotal = $sale->items->sum(fn (SaleItem $item) => (float) $item->quantity * (float) $item->unit_price);
        $discountTotal = 0;
        $taxable = $subtotal;
        $taxRate = config('sales.tax_rate', 0);
        $taxTotal = round($taxable * $taxRate, 2);
        $total = round($taxable + $taxTotal, 2);

        $sale->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
        ]);

        return $sale;
    }

    private function reserveItems(Sale $sale, Shop $shop): void
    {
        foreach ($sale->items as $item) {
            $this->inventory->reserve($item->product, $shop, (float) $item->quantity);
        }
    }

    private function releaseReservations(Sale $sale, Shop $shop): void
    {
        foreach ($sale->items as $item) {
            try {
                $this->inventory->release($item->product, $shop, (float) $item->quantity);
            } catch (InventoryException) {
                // Reservation may already be cleared.
            }
        }
    }

    private function resolveUnitPrice(Product $product, array $line, ?User $user, string $saleType = 'retail'): float
    {
        $canNegotiate = $user?->can('sales.create') ?? false;

        if ($canNegotiate && isset($line['unit_price']) && $line['unit_price'] !== '' && $line['unit_price'] !== null) {
            return (float) $line['unit_price'];
        }

        $max = (float) $product->max_selling_price;
        $min = (float) $product->min_selling_price;

        if ($saleType === 'credit') {
            if ($min > 0) {
                return $min;
            }

            return $max > 0 ? $max : 0;
        }

        if ($max > 0) {
            return $max;
        }

        if ($min > 0) {
            return $min;
        }

        return 0;
    }

    private function assertUnitPriceInRange(Product $product, float $unitPrice): void
    {
        $min = (float) $product->min_selling_price;
        $max = (float) $product->max_selling_price;

        if ($min <= 0 && $max <= 0) {
            return;
        }

        $floor = $min > 0 ? $min : 0;
        $ceiling = $max > 0 ? $max : $min;

        if ($unitPrice < $floor || $unitPrice > $ceiling) {
            throw new \InvalidArgumentException(
                "Unit price for {$product->part_number} must be between ".number_format($floor, 2).' and '.number_format($ceiling, 2).'.'
            );
        }
    }

    private function assertAvailable(Product $product, Shop $shop, float $quantity): void
    {
        if ($this->inventory->available($product, $shop) < $quantity) {
            throw new InventoryException(
                "Insufficient stock for {$product->part_number}. Available: ".
                $this->inventory->available($product, $shop)
            );
        }
    }
}
