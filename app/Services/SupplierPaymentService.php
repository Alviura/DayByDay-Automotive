<?php

namespace App\Services;

use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupplierPaymentService
{
    public function __construct(
        private SupplierApService $ap,
        private GlPostingService $gl,
    ) {}

    public function record(
        Supplier $supplier,
        float $amount,
        string $method,
        ?GoodsReceiptNote $grn = null,
        ?PurchaseOrder $po = null,
        ?string $supplierInvoiceNumber = null,
        ?string $reference = null,
        ?string $notes = null,
        ?User $user = null
    ): SupplierPayment {
        $user ??= auth()->user();

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if ($grn) {
            if (! $grn->isPosted()) {
                throw new \InvalidArgumentException('Payments can only be recorded against posted goods receipts.');
            }

            $grn->loadMissing('purchaseOrder');
            $po ??= $grn->purchaseOrder;

            if ($po && (int) $po->supplier_id !== (int) $supplier->id) {
                throw new \InvalidArgumentException('The goods receipt does not belong to this supplier.');
            }

            $balance = $this->ap->grnPayableBalance($grn);
            if ($amount > $balance + 0.001) {
                throw new \InvalidArgumentException(
                    'Payment exceeds GRN balance due (KES '.number_format($balance, 2).').'
                );
            }
        } elseif ($po) {
            if ((int) $po->supplier_id !== (int) $supplier->id) {
                throw new \InvalidArgumentException('The purchase order does not belong to this supplier.');
            }

            $balance = $this->ap->poPayableBalance($po);
            if ($amount > $balance + 0.001) {
                throw new \InvalidArgumentException(
                    'Payment exceeds PO balance due (KES '.number_format($balance, 2).').'
                );
            }
        } else {
            $balance = $this->ap->supplierPayableTotal($supplier);
            if ($amount > $balance + 0.001) {
                throw new \InvalidArgumentException(
                    'Payment exceeds supplier balance due (KES '.number_format($balance, 2).').'
                );
            }
        }

        return DB::transaction(function () use ($supplier, $amount, $method, $grn, $po, $supplierInvoiceNumber, $reference, $notes, $user) {
            if ($grn && ! $po) {
                $po = $grn->purchaseOrder;
            }

            $payment = SupplierPayment::create([
                'payment_number' => SupplierPayment::generateNumber(),
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $po?->id,
                'goods_receipt_note_id' => $grn?->id,
                'supplier_invoice_number' => $supplierInvoiceNumber,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'paid_at' => now(),
                'paid_by' => $user?->id,
                'notes' => $notes,
                'status' => 'posted',
            ]);

            $this->gl->postSupplierPayment($payment, $user);

            return $payment->fresh(['supplier', 'purchaseOrder', 'goodsReceiptNote', 'payer']);
        });
    }

    public function void(SupplierPayment $payment, ?string $reason = null, ?User $user = null): SupplierPayment
    {
        $user ??= auth()->user();

        if (! $payment->canVoid()) {
            throw new \InvalidArgumentException('This payment cannot be voided.');
        }

        return DB::transaction(function () use ($payment, $reason, $user) {
            $payment->update([
                'status' => 'voided',
                'voided_by' => $user?->id,
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);

            return $payment->fresh(['supplier', 'purchaseOrder', 'goodsReceiptNote', 'payer', 'voidedBy']);
        });
    }
}
