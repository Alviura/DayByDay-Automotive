<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\QuotationSeries;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class NavigationBadgeService
{
    public function __construct(
        private ApprovalService $approvals,
        private TransferRequestAccessService $transferRequests,
        private StockTransferAccessService $stockTransfers,
    ) {}

    /**
     * @return array<string, array{count: int, url: string}>
     */
    public function forUser(User $user): array
    {
        $badges = [];

        if ($badge = $this->approvalsBadge($user)) {
            $badges['approvals'] = $badge;
        }

        if ($badge = $this->inventoryBadge($user)) {
            $badges['inventory'] = $badge;
        }

        if ($badge = $this->transferRequestsBadge($user)) {
            $badges['transfer_requests'] = $badge;
        }

        if ($badge = $this->stockTransfersBadge($user)) {
            $badges['stock_transfers'] = $badge;
        }

        if ($badge = $this->quotationSeriesBadge($user)) {
            $badges['quotation_series'] = $badge;
        }

        if ($badge = $this->purchaseOrdersBadge($user)) {
            $badges['purchase_orders'] = $badge;
        }

        if ($badge = $this->customerReturnsBadge($user)) {
            $badges['customer_returns'] = $badge;
        }

        if ($badge = $this->supplierReturnsBadge($user)) {
            $badges['supplier_returns'] = $badge;
        }

        if ($badge = $this->cashDeskBadge($user)) {
            $badges['cash_desk'] = $badge;
        }

        if ($badge = $this->journalEntriesBadge($user)) {
            $badges['journal_entries'] = $badge;
        }

        return $badges;
    }

  /**
     * @return array{count: int, url: string}|null
     */
    private function approvalsBadge(User $user): ?array
    {
        if (! $user->can('approvals.act')) {
            return null;
        }

        $count = $this->approvals->pendingCountFor($user);

        return $count > 0
            ? ['count' => $count, 'url' => route('approvals.index')]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function inventoryBadge(User $user): ?array
    {
        if (! $user->can('inventory.view')) {
            return null;
        }

        $count = $this->lowStockCount($user);

        return $count > 0
            ? ['count' => $count, 'url' => route('inventory.index', ['filter' => 'low_stock'])]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function transferRequestsBadge(User $user): ?array
    {
        if (! $user->can('transfer_requests.view')) {
            return null;
        }

        $count = $this->transferRequests->pendingReviewCount($user);

        return $count > 0
            ? ['count' => $count, 'url' => route('transfer-requests.index', ['status' => 'submitted'])]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function stockTransfersBadge(User $user): ?array
    {
        if (! $user->can('transfers.view')) {
            return null;
        }

        $pendingApproval = $this->stockTransfers->pendingApprovalCount($user);
        $pendingDispatch = $this->stockTransfers->pendingDispatchCount($user);
        $awaitingReceive = $this->stockTransfers->awaitingReceiveCount($user);
        $count = max($pendingApproval, $pendingDispatch, $awaitingReceive);

        if ($count === 0) {
            return null;
        }

        $url = $pendingApproval > 0
            ? route('stock-transfers.index', ['status' => 'pending'])
            : ($pendingDispatch > 0
                ? route('stock-transfers.index', ['status' => 'approved'])
                : route('stock-transfers.index', ['status' => 'in_transit']));

        return ['count' => $count, 'url' => $url];
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function quotationSeriesBadge(User $user): ?array
    {
        if (! $user->can('procurement.view')) {
            return null;
        }

        $count = QuotationSeries::query()
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->count();

        return $count > 0
            ? ['count' => $count, 'url' => route('quotation-series.index')]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function purchaseOrdersBadge(User $user): ?array
    {
        if (! $user->can('procurement.view')) {
            return null;
        }

        $query = PurchaseOrder::query()
            ->whereIn('status', ['sent', 'partially_received']);

        if ($user->hasRole('Warehouse Manager') && $user->warehouse_id) {
            // Warehouse managers focus on POs awaiting receipt.
        }

        $count = $query->count();

        return $count > 0
            ? ['count' => $count, 'url' => route('purchase-orders.index', ['status' => 'sent'])]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function customerReturnsBadge(User $user): ?array
    {
        if (! $user->can('returns.view')) {
            return null;
        }

        $query = ReturnRecord::query()
            ->where('type', 'customer')
            ->where('status', 'pending');

        if ($user->hasRole('Shop Manager') && $user->shop_id) {
            $query->where('shop_id', $user->shop_id);
        }

        $count = $query->count();

        return $count > 0
            ? ['count' => $count, 'url' => route('customer-returns.index', ['status' => 'pending'])]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function supplierReturnsBadge(User $user): ?array
    {
        if (! $user->can('returns.view')) {
            return null;
        }

        $count = ReturnRecord::query()
            ->where('type', 'supplier')
            ->where('status', 'pending')
            ->count();

        return $count > 0
            ? ['count' => $count, 'url' => route('supplier-returns.index', ['status' => 'pending'])]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function cashDeskBadge(User $user): ?array
    {
        if (! $user->can('sales.create')) {
            return null;
        }

        $query = Sale::query()->where('status', 'held');

        if ($user->hasRole('Shop Manager') && $user->shop_id) {
            $query->where('shop_id', $user->shop_id);
        } elseif ($user->hasRole('Shop Attendant') && $user->shop_id) {
            $query->where('shop_id', $user->shop_id);
        }

        $count = $query->count();

        return $count > 0
            ? ['count' => $count, 'url' => route('sales.desk')]
            : null;
    }

    /**
     * @return array{count: int, url: string}|null
     */
    private function journalEntriesBadge(User $user): ?array
    {
        if (! $user->can('finance.view') || ! $user->can('approvals.act')) {
            return null;
        }

        $count = Approval::query()
            ->pending()
            ->where('approvable_type', JournalEntry::class)
            ->when(
                ! $user->hasRole(config('approvals.default_approver_role')),
                fn ($q) => $q->where('current_approver_id', $user->id)
            )
            ->count();

        return $count > 0
            ? ['count' => $count, 'url' => route('journal-entries.index', ['status' => 'pending_approval'])]
            : null;
    }

    private function lowStockCount(User $user): int
    {
        $aggregated = StockBalance::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity_on_hand) as total_on_hand')
            ->when($user->shop_id && $user->hasRole(['Shop Manager', 'Shop Attendant']), function ($q) use ($user) {
                $q->where('location_type', Shop::class)->where('location_id', $user->shop_id);
            })
            ->when($user->warehouse_id && $user->hasRole('Warehouse Manager'), function ($q) use ($user) {
                $q->where('location_type', Warehouse::class)->where('location_id', $user->warehouse_id);
            })
            ->groupBy('product_id');

        return (int) Product::query()
            ->joinSub($aggregated, 'agg', fn ($join) => $join->on('products.id', '=', 'agg.product_id'))
            ->where('products.reorder_level', '>', 0)
            ->whereColumn('agg.total_on_hand', '<=', 'products.reorder_level')
            ->where('agg.total_on_hand', '>', 0)
            ->count();
    }
}
