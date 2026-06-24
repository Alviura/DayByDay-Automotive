<?php

namespace App\Services;

use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Collection;

class SupplierApService
{
    public function supplierPayableTotal(Supplier $supplier): float
    {
        $open = $this->fifoGrnBalancesForSupplier($supplier->id)->sum();
        $credits = $this->supplierReturnCredits($supplier->id);

        return max(0, round($open - $credits, 2));
    }

    public function grnPayableBalance(GoodsReceiptNote $grn): float
    {
        if (! $grn->isPosted()) {
            return 0;
        }

        $grn->loadMissing('purchaseOrder');
        $supplierId = $grn->purchaseOrder?->supplier_id;

        if (! $supplierId) {
            return $this->directGrnBalance($grn);
        }

        return (float) ($this->fifoGrnBalancesForSupplier($supplierId)[$grn->id] ?? 0);
    }

    public function poPayableBalance(PurchaseOrder $po): float
    {
        $balances = $this->fifoGrnBalancesForPurchaseOrder($po->id);

        return max(0, round($balances->sum(), 2));
    }

    /**
     * @return Collection<int, float> keyed by goods_receipt_note_id
     */
    public function fifoGrnBalancesForSupplier(int $supplierId): Collection
    {
        $grns = GoodsReceiptNote::query()
            ->posted()
            ->whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $supplierId))
            ->with('items')
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();

        $pool = (float) SupplierPayment::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'posted')
            ->whereNull('goods_receipt_note_id')
            ->sum('amount');

        return $this->applyFifoPool($grns, $pool);
    }

    /**
     * @return Collection<int, float> keyed by goods_receipt_note_id
     */
    public function fifoGrnBalancesForPurchaseOrder(int $purchaseOrderId): Collection
    {
        $grns = GoodsReceiptNote::query()
            ->posted()
            ->where('purchase_order_id', $purchaseOrderId)
            ->with('items')
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();

        $pool = (float) SupplierPayment::query()
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('status', 'posted')
            ->whereNull('goods_receipt_note_id')
            ->sum('amount');

        return $this->applyFifoPool($grns, $pool);
    }

    public function postedGrnValueForSupplier(int $supplierId): float
    {
        return (float) GoodsReceiptNote::query()
            ->posted()
            ->whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $supplierId))
            ->with('items')
            ->get()
            ->sum(fn (GoodsReceiptNote $grn) => $grn->totalValue());
    }

    public function postedPaymentsForSupplier(int $supplierId): float
    {
        return (float) SupplierPayment::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'posted')
            ->sum('amount');
    }

    public function supplierReturnCredits(int $supplierId): float
    {
        return (float) ReturnRecord::query()
            ->where('type', 'supplier')
            ->where('supplier_id', $supplierId)
            ->where('status', 'completed')
            ->sum('refund_amount');
    }

    public function totalOutstanding(): float
    {
        return round(
            Supplier::active()->get()->sum(fn (Supplier $supplier) => $this->supplierPayableTotal($supplier)),
            2
        );
    }

    public function monthlySpend(Supplier $supplier, int $months = 6): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        return SupplierPayment::query()
            ->where('supplier_id', $supplier->id)
            ->where('status', 'posted')
            ->where('paid_at', '>=', $start)
            ->selectRaw('YEAR(paid_at) as yr, MONTH(paid_at) as mo, SUM(amount) as total')
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get()
            ->mapWithKeys(fn ($row) => [
                sprintf('%04d-%02d', $row->yr, $row->mo) => (float) $row->total,
            ])
            ->all();
    }

    private function directGrnBalance(GoodsReceiptNote $grn): float
    {
        $grn->loadMissing('items');

        $value = (float) $grn->totalValue();
        $paid = (float) SupplierPayment::query()
            ->where('goods_receipt_note_id', $grn->id)
            ->where('status', 'posted')
            ->sum('amount');

        return max(0, round($value - $paid, 2));
    }

    /**
     * @param  Collection<int, GoodsReceiptNote>  $grns
     * @return Collection<int, float> keyed by goods_receipt_note_id
     */
    private function applyFifoPool(Collection $grns, float $pool): Collection
    {
        $balances = collect();

        foreach ($grns as $grn) {
            $balance = $this->directGrnBalance($grn);

            if ($balance > 0 && $pool > 0) {
                $applied = min($balance, $pool);
                $balance = round($balance - $applied, 2);
                $pool = round($pool - $applied, 2);
            }

            $balances[$grn->id] = $balance;
        }

        return $balances;
    }
}
