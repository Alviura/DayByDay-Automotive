<?php

namespace App\Services;

use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QuotationSeries;
use App\Models\ReturnRecord;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierOverviewService
{
    /**
     * @return array{
     *     stats: array<string, float|int|string|null>,
     *     poPipeline: list<array{key: string, label: string, icon: string, count: int}>,
     *     seriesPipeline: list<array{key: string, label: string, icon: string, count: int}>,
     *     monthlySpend: \Illuminate\Support\Collection,
     *     topProducts: \Illuminate\Support\Collection,
     *     quotationSeries: \Illuminate\Support\Collection,
     *     purchaseOrders: \Illuminate\Support\Collection,
     *     goodsReceipts: \Illuminate\Support\Collection,
     *     supplierReturns: \Illuminate\Support\Collection,
     *     openPurchaseOrders: \Illuminate\Support\Collection
     * }
     */
    public function context(Supplier $supplier): array
    {
        $poBase = PurchaseOrder::query()->where('supplier_id', $supplier->id);
        $seriesBase = QuotationSeries::query()->where('supplier_id', $supplier->id);

        $lifetimeSpend = (float) (clone $poBase)
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->sum('total');

        $completedPoCount = (clone $poBase)
            ->whereIn('status', ['received', 'closed_short'])
            ->count();

        $openPoValue = (float) PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q
                ->where('supplier_id', $supplier->id)
                ->whereIn('status', ['sent', 'partially_received']))
            ->get()
            ->sum(fn (PurchaseOrderItem $item) => $item->remainingQuantity() * (float) $item->unit_cost);

        $receivedValue = (float) PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->selectRaw('SUM(received_quantity * unit_cost) as value')
            ->value('value');

        $grnCount = GoodsReceiptNote::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->count();

        $returnCount = ReturnRecord::query()
            ->where('type', 'supplier')
            ->where('supplier_id', $supplier->id)
            ->count();

        $stats = [
            'quotation_series_total' => (clone $seriesBase)->count(),
            'quotation_series_open' => (clone $seriesBase)->whereNotIn('status', ['closed', 'cancelled'])->count(),
            'purchase_orders_total' => (clone $poBase)->count(),
            'purchase_orders_open' => (clone $poBase)->whereIn('status', ['sent', 'partially_received'])->count(),
            'lifetime_spend' => $lifetimeSpend,
            'open_po_value' => $openPoValue,
            'received_value' => $receivedValue,
            'grn_count' => $grnCount,
            'return_count' => $returnCount,
            'avg_order_value' => $completedPoCount > 0
                ? round($lifetimeSpend / max(1, (clone $poBase)->whereNotIn('status', ['cancelled', 'draft'])->count()), 2)
                : 0.0,
            'last_order_date' => (clone $poBase)->max('order_date'),
            'first_order_date' => (clone $poBase)->min('order_date'),
        ];

        $poPipeline = $this->poPipeline($supplier->id);
        $seriesPipeline = $this->seriesPipeline($supplier->id);

        $monthlySpend = (clone $poBase)
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->where('order_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('DATE_FORMAT(order_date, "%Y-%m") as month, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $topProducts = PurchaseOrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(line_total) as total_value'))
            ->whereHas('purchaseOrder', fn ($q) => $q
                ->where('supplier_id', $supplier->id)
                ->whereNotIn('status', ['cancelled', 'draft']))
            ->with('product.unit')
            ->groupBy('product_id')
            ->orderByDesc('total_value')
            ->limit(8)
            ->get();

        $quotationSeries = (clone $seriesBase)
            ->withCount('items')
            ->latest()
            ->limit(12)
            ->get();

        $purchaseOrders = (clone $poBase)
            ->with(['quotationSeries'])
            ->withCount('items')
            ->latest('order_date')
            ->limit(12)
            ->get();

        $openPurchaseOrders = (clone $poBase)
            ->whereIn('status', ['sent', 'partially_received'])
            ->withCount('items')
            ->latest('expected_date')
            ->limit(6)
            ->get();

        $goodsReceipts = GoodsReceiptNote::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->with(['purchaseOrder', 'warehouse'])
            ->withCount('items')
            ->latest('received_at')
            ->limit(10)
            ->get();

        $supplierReturns = ReturnRecord::query()
            ->where('type', 'supplier')
            ->where('supplier_id', $supplier->id)
            ->with(['warehouse'])
            ->withCount('items')
            ->latest()
            ->limit(8)
            ->get();

        return compact(
            'stats',
            'poPipeline',
            'seriesPipeline',
            'monthlySpend',
            'topProducts',
            'quotationSeries',
            'purchaseOrders',
            'goodsReceipts',
            'supplierReturns',
            'openPurchaseOrders',
        );
    }

    /**
     * @return list<array{key: string, label: string, icon: string, count: int}>
     */
    private function poPipeline(int $supplierId): array
    {
        $counts = PurchaseOrder::query()
            ->where('supplier_id', $supplierId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            ['key' => 'sent', 'label' => 'Sent', 'icon' => 'fa-paper-plane', 'count' => (int) ($counts['sent'] ?? 0)],
            ['key' => 'partially_received', 'label' => 'Partial', 'icon' => 'fa-truck-ramp-box', 'count' => (int) ($counts['partially_received'] ?? 0)],
            ['key' => 'received', 'label' => 'Received', 'icon' => 'fa-circle-check', 'count' => (int) ($counts['received'] ?? 0)],
            ['key' => 'closed_short', 'label' => 'Closed Short', 'icon' => 'fa-scissors', 'count' => (int) ($counts['closed_short'] ?? 0)],
        ];
    }

    /**
     * @return list<array{key: string, label: string, icon: string, count: int}>
     */
    private function seriesPipeline(int $supplierId): array
    {
        $open = QuotationSeries::query()
            ->where('supplier_id', $supplierId)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->count();

        $inTransit = QuotationSeries::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'in_transit')
            ->count();

        $closed = QuotationSeries::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'closed')
            ->count();

        $pending = QuotationSeries::query()
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['pending_approval', 'cost_analysis', 'order_draft', 'quotation_draft', 'draft'])
            ->count();

        return [
            ['key' => 'open', 'label' => 'Open', 'icon' => 'fa-folder-open', 'count' => $open],
            ['key' => 'pending', 'label' => 'In Progress', 'icon' => 'fa-pen', 'count' => $pending],
            ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-ship', 'count' => $inTransit],
            ['key' => 'closed', 'label' => 'Closed', 'icon' => 'fa-flag-checkered', 'count' => $closed],
        ];
    }
}
