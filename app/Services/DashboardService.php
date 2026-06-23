<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\CustomerInvoice;
use App\Models\GoodsReceiptNote;
use App\Models\Product;
use App\Models\QuotationSeries;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Models\StockTransfer;
use App\Models\TaxRemittance;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private ApprovalService $approvals,
        private InventoryService $inventory,
        private LocationOverviewService $locations,
        private TransferRequestAccessService $transferRequests,
        private StockTransferAccessService $stockTransfers,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $role = $this->resolveRole($user);

        return match ($role) {
            'admin' => $this->adminDashboard($user),
            'warehouse' => $this->warehouseDashboard($user),
            'shop_manager' => $this->shopManagerDashboard($user),
            'attendant' => $this->attendantDashboard($user),
            default => $this->adminDashboard($user),
        };
    }

    public function resolveRole(User $user): string
    {
        if ($user->hasRole('Administrator')) {
            return 'admin';
        }

        if ($user->hasRole('Warehouse Manager')) {
            return 'warehouse';
        }

        if ($user->hasRole('Shop Manager')) {
            return 'shop_manager';
        }

        if ($user->hasRole('Shop Attendant')) {
            return 'attendant';
        }

        return 'admin';
    }

    /**
     * @return array<string, mixed>
     */
    private function adminDashboard(User $user): array
    {
        $inventory = $this->inventory->indexStats();
        $salesToday = $this->salesSnapshot(null);
        $salesMonth = $this->salesSnapshot(null, now()->startOfMonth(), now()->endOfDay());

        $pendingApprovals = $user->can('approvals.act')
            ? $this->approvals->pendingCountFor($user)
            : 0;

        $approvalPipeline = $user->can('approvals.act')
            ? $this->buildApprovalPipeline($user)
            : [];

        $pendingApprovalsList = $user->can('approvals.act')
            ? $this->pendingApprovalsList($user, 6)
            : collect();

        $inTransit = $this->inTransitCount();
        $pendingDispatch = StockTransfer::where('status', 'approved')->count();
        $transferRequestsPending = TransferRequest::where('status', 'submitted')->count();
        $openSeries = QuotationSeries::whereNotIn('status', ['closed', 'cancelled'])->count();
        $outstandingInvoices = (float) CustomerInvoice::whereIn('status', ['sent', 'partially_paid'])
            ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as balance')
            ->value('balance');
        $salesTrend = $this->salesMonthTrend(null);
        $approvalModules = $user->can('approvals.act')
            ? count($this->approvals->pendingCountByModule($user))
            : 0;

        $commandStrip = [
            'cards' => [
                $this->richKpi(
                    'Pending Approvals',
                    $pendingApprovals,
                    'fa-inbox',
                    $pendingApprovals > 0 ? 'red' : 'purple',
                    array_filter([
                        $approvalModules > 0 ? ['text' => $approvalModules.' modules', 'tone' => 'purple'] : null,
                        $pendingApprovals > 0 ? ['text' => 'Action required', 'tone' => 'red'] : ['text' => 'Inbox clear', 'tone' => 'green'],
                    ]),
                    null,
                    $pendingApprovals > 0 ? ['text' => 'Needs attention'] : null,
                    $pendingApprovals > 0 ? route('approvals.index') : null,
                ),
                $this->richKpi(
                    'Month Revenue',
                    number_format($salesMonth['revenue'], 0),
                    'fa-coins',
                    'teal',
                    [
                        ['text' => $salesMonth['count'].' completed', 'tone' => 'green'],
                        ['text' => 'Today '.number_format($salesToday['revenue'], 0), 'tone' => 'gray'],
                    ],
                    $salesTrend,
                    null,
                    route('sales.index'),
                    'KES ',
                ),
                $this->richKpi(
                    'Inventory Value',
                    number_format($inventory['value'], 0),
                    'fa-boxes-stacked',
                    'blue',
                    [
                        ['text' => number_format($inventory['units'], 0).' units', 'tone' => 'blue'],
                        ['text' => $inventory['low_stock'].' low stock', 'tone' => $inventory['low_stock'] > 0 ? 'amber' : 'green'],
                    ],
                    null,
                    $inventory['low_stock'] > 0 ? ['text' => 'Reorder attention needed'] : null,
                    route('inventory.index'),
                    'KES ',
                ),
                $this->richKpi(
                    'Distribution',
                    $inTransit,
                    'fa-truck',
                    'green',
                    array_filter([
                        $pendingDispatch > 0 ? ['text' => $pendingDispatch.' to dispatch', 'tone' => 'amber'] : null,
                        $transferRequestsPending > 0 ? ['text' => $transferRequestsPending.' requests', 'tone' => 'purple'] : null,
                        ($pendingDispatch === 0 && $transferRequestsPending === 0) ? ['text' => 'Pipeline clear', 'tone' => 'green'] : null,
                    ]),
                    null,
                    ($pendingDispatch + $transferRequestsPending) > 0 ? ['text' => 'Transfers need action'] : null,
                    route('stock-transfers.index'),
                ),
            ],
            'summary' => array_filter([
                $this->summaryItem('Fleet Receivables', 'KES '.number_format($outstandingInvoices, 0), 'Outstanding invoices', $outstandingInvoices > 0 ? 'amber' : 'green', route('customer-invoices.index')),
                $this->summaryItem('Procurement', (string) $openSeries, 'Open quotation series', 'orange', route('quotation-series.index')),
                $this->summaryItem(
                    'Active Transfers',
                    (string) $inTransit,
                    $pendingDispatch > 0 ? "{$pendingDispatch} awaiting dispatch" : 'In transit network-wide',
                    $inTransit > 0 || $pendingDispatch > 0 ? 'blue' : 'green',
                    route('stock-transfers.index', ['status' => 'in_transit'])
                ),
                $this->systemStatusSummary($pendingApprovals, $inventory['low_stock']),
            ]),
        ];

        return [
            'role' => 'admin',
            'greeting' => $this->greeting($user),
            'subtitle' => 'System-wide overview across operations, inventory, and finance.',
            'heroHighlights' => array_filter([
                $pendingApprovals > 0
                    ? ['label' => 'Approvals', 'value' => $pendingApprovals, 'icon' => 'fa-inbox']
                    : null,
                ['label' => 'Month Revenue', 'value' => 'KES '.number_format($salesMonth['revenue'], 0), 'icon' => 'fa-coins'],
                ['label' => 'Inventory', 'value' => 'KES '.number_format($inventory['value'], 0), 'icon' => 'fa-boxes-stacked'],
            ]),
            'commandStrip' => $commandStrip,
            'approvalPipeline' => $approvalPipeline,
            'pendingApprovalsList' => $pendingApprovalsList,
            'recentSales' => $this->recentSales(null, 6),
            'activeTransfers' => $this->activeTransfers(6),
            'lowStockProducts' => $this->globalLowStock(6),
            'recentProcurement' => $this->recentOpenSeries(6),
            'chartData' => [
                'monthly' => $this->salesMonthlyChart(null),
                'procurement' => $this->procurementPipelineChart(),
                'revenueByShop' => $this->revenueByShopChart(),
            ],
            'quickActions' => $this->quickActionsFor($user, 'admin'),
            'showChart' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function shopManagerDashboard(User $user): array
    {
        $shop = $user->shop;
        $shopId = $shop?->id;

        $inventory = $shop
            ? $this->locations->inventoryContext($shop)['totals']
            : ['on_hand' => 0, 'value' => 0, 'low_stock_count' => 0, 'sku_count' => 0];

        $activity = $shop
            ? $this->locations->shopActivity($shop)
            : ['stats' => ['held' => 0, 'completed_today' => 0, 'today_total' => 0, 'avg_ticket_today' => 0], 'heldSales' => collect(), 'recentSales' => collect(), 'lowStock' => collect()];

        $stats = $activity['stats'];
        $salesMonth = $this->salesSnapshot($shopId, now()->startOfMonth(), now()->endOfDay());
        $pendingReviews = $this->transferRequests->pendingReviewCount($user);
        $inTransit = $this->inTransitCount($user);
        $pendingDispatch = $this->stockTransfers->pendingDispatchCount($user);

        $lowStock = $shop
            ? $this->locations->inventoryContext($shop)['lowStock']->take(6)
            : collect();

        $outstandingInvoices = $user->can('customer_invoices.view')
            ? (float) CustomerInvoice::whereIn('status', ['sent', 'partially_paid'])
                ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as balance')
                ->value('balance')
            : null;

        $salesTrend = $this->salesMonthTrend($shopId);

        $commandStrip = [
            'cards' => array_filter([
                $this->richKpi(
                    "Today's Sales",
                    $stats['completed_today'],
                    'fa-receipt',
                    'blue',
                    [
                        ['text' => 'KES '.number_format($stats['today_total'], 0), 'tone' => 'teal'],
                        ['text' => 'Avg '.number_format($stats['avg_ticket_today'], 0), 'tone' => 'gray'],
                    ],
                    null,
                    null,
                    route('sales.index'),
                ),
                $this->richKpi(
                    'Cash Desk',
                    $stats['held'],
                    'fa-hourglass-half',
                    $stats['held'] > 0 ? 'red' : 'amber',
                    [
                        ['text' => $stats['held'] > 0 ? 'Orders waiting' : 'Desk clear', 'tone' => $stats['held'] > 0 ? 'red' : 'green'],
                    ],
                    null,
                    $stats['held'] > 0 ? ['text' => 'Checkout required'] : null,
                    route('sales.desk'),
                ),
                $this->richKpi(
                    'Month Revenue',
                    number_format($salesMonth['revenue'], 0),
                    'fa-chart-line',
                    'green',
                    [
                        ['text' => $salesMonth['count'].' completed', 'tone' => 'green'],
                        ['text' => now()->format('F'), 'tone' => 'gray'],
                    ],
                    $salesTrend,
                    null,
                    route('sales.index'),
                    'KES ',
                ),
                $shop ? $this->richKpi(
                    'Shop Stock',
                    number_format($inventory['value'], 0),
                    'fa-boxes-stacked',
                    'teal',
                    [
                        ['text' => number_format($inventory['on_hand'], 0).' on hand', 'tone' => 'blue'],
                        ['text' => $inventory['low_stock_count'].' low stock', 'tone' => $inventory['low_stock_count'] > 0 ? 'amber' : 'green'],
                    ],
                    null,
                    $inventory['low_stock_count'] > 0 ? ['text' => 'Stock running low'] : null,
                    route('inventory.index'),
                    'KES ',
                ) : null,
            ]),
            'summary' => array_filter([
                $this->summaryItem('Transfer Reviews', (string) $pendingReviews, 'Requests awaiting you', $pendingReviews > 0 ? 'amber' : 'green', route('transfer-requests.index', ['status' => 'submitted'])),
                $this->summaryItem('In Transit', (string) $inTransit, 'Inbound or outbound', $inTransit > 0 ? 'blue' : 'default', route('stock-transfers.index', ['status' => 'in_transit'])),
                $this->summaryItem('Awaiting Dispatch', (string) $pendingDispatch, 'Approved transfers', $pendingDispatch > 0 ? 'orange' : 'default', route('stock-transfers.index', ['status' => 'approved'])),
                $outstandingInvoices !== null
                    ? $this->summaryItem('Fleet Invoices', 'KES '.number_format($outstandingInvoices, 0), 'Outstanding balance', $outstandingInvoices > 0 ? 'amber' : 'green', route('customer-invoices.index'))
                    : $this->summaryItem('Shop Status', $shop?->code ?? '—', $shop?->name ?? 'Your location', 'blue'),
            ]),
        ];

        return [
            'role' => 'shop_manager',
            'greeting' => $this->greeting($user),
            'subtitle' => $shop ? "Managing {$shop->name}" : 'Your shop dashboard',
            'heroHighlights' => [],
            'location' => $shop,
            'commandStrip' => $commandStrip,
            'heldSales' => $activity['heldSales'],
            'recentSales' => $activity['recentSales']->take(8),
            'lowStock' => $lowStock,
            'chartData' => ['monthly' => $this->salesMonthlyChart($shopId)],
            'quickActions' => $this->quickActionsFor($user, 'shop_manager'),
            'showChart' => $user->can('sales.view'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attendantDashboard(User $user): array
    {
        $shop = $user->shop;
        $shopId = $shop?->id;

        $activity = $shop
            ? $this->locations->shopActivity($shop)
            : ['stats' => ['held' => 0, 'completed_today' => 0, 'today_total' => 0, 'avg_ticket_today' => 0], 'heldSales' => collect(), 'recentSales' => collect()];

        $stats = $activity['stats'];
        $lastSale = $activity['recentSales']->first();

        $commandStrip = [
            'cards' => array_filter([
                $this->richKpi(
                    "Today's Sales",
                    $stats['completed_today'],
                    'fa-receipt',
                    'blue',
                    [
                        ['text' => 'KES '.number_format($stats['today_total'], 0), 'tone' => 'teal'],
                    ],
                    null,
                    null,
                    route('sales.index'),
                ),
                $this->richKpi(
                    'Cash Desk',
                    $stats['held'],
                    'fa-cash-register',
                    $stats['held'] > 0 ? 'red' : 'amber',
                    [
                        ['text' => $stats['held'] > 0 ? 'Waiting checkout' : 'Desk clear', 'tone' => $stats['held'] > 0 ? 'red' : 'green'],
                    ],
                    null,
                    $stats['held'] > 0 ? ['text' => 'Open cash desk'] : null,
                    route('sales.desk'),
                ),
                $this->richKpi(
                    'Avg Ticket',
                    number_format($stats['avg_ticket_today'], 0),
                    'fa-coins',
                    'green',
                    [
                        ['text' => 'Today average', 'tone' => 'gray'],
                    ],
                    null,
                    null,
                    null,
                    'KES ',
                ),
                $shop ? $this->richKpi(
                    'My Shop',
                    $shop->code,
                    'fa-store',
                    'purple',
                    [
                        ['text' => $shop->name, 'tone' => 'gray'],
                    ],
                    null,
                    null,
                    null,
                ) : null,
            ]),
            'summary' => [
                $this->summaryItem('Completed Today', (string) $stats['completed_today'], 'Sales checked out', 'green'),
                $this->summaryItem('Held Queue', (string) $stats['held'], $stats['held'] > 0 ? 'Orders at desk' : 'No waiting orders', $stats['held'] > 0 ? 'amber' : 'green', route('sales.desk')),
                $this->summaryItem('Last Sale', $lastSale ? $lastSale->sold_at?->diffForHumans(short: true) ?? '—' : '—', $lastSale?->receipt_number ?? 'No sales yet', 'default'),
                $this->summaryItem('Desk Status', $stats['held'] > 0 ? 'Busy' : 'Ready', $stats['held'] > 0 ? "{$stats['held']} order(s) waiting" : 'Open for checkout', 'default', null, $stats['held'] > 0 ? 'warn' : 'ok'),
            ],
        ];

        return [
            'role' => 'attendant',
            'greeting' => $this->greeting($user),
            'subtitle' => $shop ? "Cash desk at {$shop->name}" : 'Your sales workspace',
            'heroHighlights' => [],
            'location' => $shop,
            'commandStrip' => $commandStrip,
            'heldSales' => $activity['heldSales'],
            'recentSales' => $activity['recentSales']->take(6),
            'quickActions' => $this->quickActionsFor($user, 'attendant'),
            'showChart' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function warehouseDashboard(User $user): array
    {
        $warehouse = $user->warehouse;
        $incoming = $this->inventory->incomingFromPurchaseOrders();

        $inventory = $warehouse
            ? $this->locations->inventoryContext($warehouse)['totals']
            : ['on_hand' => 0, 'value' => 0, 'low_stock_count' => 0, 'sku_count' => 0];

        $activity = $warehouse
            ? $this->locations->warehouseActivity($warehouse)
            : ['transfers' => collect(), 'receipts' => collect(), 'returns' => collect()];

        $pendingReviews = $this->transferRequests->pendingReviewCount($user);
        $pendingDispatch = $this->stockTransfers->pendingDispatchCount($user);
        $inTransit = $this->inTransitCount($user);
        $openSeries = QuotationSeries::whereNotIn('status', ['closed', 'cancelled'])->count();

        $lowStock = $warehouse
            ? $this->locations->inventoryContext($warehouse)['lowStock']->take(6)
            : collect();

        $dispatchQueue = StockTransfer::query()
            ->where('status', 'approved')
            ->when($warehouse, function ($q) use ($warehouse) {
                $q->where('source_type', Warehouse::class)->where('source_id', $warehouse->id);
            })
            ->with(['destination'])
            ->latest()
            ->limit(6)
            ->get();

        $grnThisMonth = GoodsReceiptNote::query()
            ->when($warehouse, fn ($q) => $q->where('warehouse_id', $warehouse->id))
            ->whereMonth('received_at', now()->month)
            ->whereYear('received_at', now()->year)
            ->count();

        $commandStrip = [
            'cards' => array_filter([
                $warehouse ? $this->richKpi(
                    'Warehouse Stock',
                    number_format($inventory['on_hand'], 0),
                    'fa-cubes',
                    'blue',
                    [
                        ['text' => number_format($inventory['sku_count']).' SKUs', 'tone' => 'blue'],
                        ['text' => 'KES '.number_format($inventory['value'], 0), 'tone' => 'teal'],
                    ],
                    null,
                    $inventory['low_stock_count'] > 0 ? ['text' => $inventory['low_stock_count'].' items low'] : null,
                    route('inventory.index'),
                ) : null,
                $this->richKpi(
                    'Incoming PO',
                    number_format($incoming['units'], 0),
                    'fa-truck-ramp-box',
                    'amber',
                    [
                        ['text' => $incoming['lines'].' open lines', 'tone' => 'amber'],
                    ],
                    null,
                    null,
                    route('purchase-orders.index'),
                ),
                $this->richKpi(
                    'Transfers',
                    $inTransit,
                    'fa-truck',
                    'green',
                    array_filter([
                        $pendingDispatch > 0 ? ['text' => $pendingDispatch.' to dispatch', 'tone' => 'red'] : null,
                        $inTransit > 0 ? ['text' => $inTransit.' in transit', 'tone' => 'green'] : null,
                        ($pendingDispatch === 0 && $inTransit === 0) ? ['text' => 'No active moves', 'tone' => 'gray'] : null,
                    ]),
                    null,
                    $pendingDispatch > 0 ? ['text' => 'Dispatch queue active'] : null,
                    route('stock-transfers.index'),
                ),
                $this->richKpi(
                    'Requests',
                    $pendingReviews,
                    'fa-inbox',
                    $pendingReviews > 0 ? 'red' : 'purple',
                    [
                        ['text' => $pendingReviews > 0 ? 'Awaiting review' : 'Inbox clear', 'tone' => $pendingReviews > 0 ? 'red' : 'green'],
                    ],
                    null,
                    $pendingReviews > 0 ? ['text' => 'Review transfer requests'] : null,
                    route('transfer-requests.index', ['status' => 'submitted']),
                ),
            ]),
            'summary' => [
                $this->summaryItem('Open Series', (string) $openSeries, 'Procurement pipeline', 'orange', route('quotation-series.index')),
                $this->summaryItem('GRNs This Month', (string) $grnThisMonth, 'Goods received', 'green', route('goods-receipts.index')),
                $this->summaryItem('Low Stock', (string) $inventory['low_stock_count'], 'Below reorder level', $inventory['low_stock_count'] > 0 ? 'amber' : 'green', route('inventory.index')),
                $this->summaryItem('Warehouse Status', ($pendingReviews + $pendingDispatch) > 0 ? 'Action needed' : 'Operational', $warehouse?->name ?? 'Distribution hub', 'default', null, ($pendingReviews + $pendingDispatch) > 0 ? 'warn' : 'ok'),
            ],
        ];

        return [
            'role' => 'warehouse',
            'greeting' => $this->greeting($user),
            'subtitle' => $warehouse ? "Warehouse hub — {$warehouse->name}" : 'Distribution & procurement',
            'heroHighlights' => [],
            'location' => $warehouse,
            'commandStrip' => $commandStrip,
            'lowStock' => $lowStock,
            'dispatchQueue' => $dispatchQueue,
            'recentReceipts' => $activity['receipts']->take(6),
            'recentTransfers' => $activity['transfers']->take(6),
            'quickActions' => $this->quickActionsFor($user, 'warehouse'),
            'showChart' => false,
        ];
    }

    /**
     * @param list<array{text: string, tone: string}> $badges
     * @param array{direction: string, text: string}|null $trend
     * @param array{text: string}|null $alert
     * @return array<string, mixed>
     */
    private function richKpi(
        string $label,
        string|int|float $value,
        string $icon,
        string $color,
        array $badges = [],
        ?array $trend = null,
        ?array $alert = null,
        ?string $url = null,
        string $prefix = '',
        ?string $sub = null,
    ): array {
        return [
            'label' => $label,
            'value' => $value,
            'prefix' => $prefix,
            'icon' => $icon,
            'color' => $color,
            'badges' => array_values(array_filter($badges)),
            'trend' => $trend,
            'alert' => $alert,
            'url' => $url,
            'sub' => $sub,
        ];
    }

    /**
     * @return array{label: string, value: string, sub: string, tone: string, url: ?string, status?: string}
     */
    private function summaryItem(
        string $label,
        string $value,
        string $sub,
        string $tone = 'default',
        ?string $url = null,
        ?string $status = null,
    ): array {
        $item = compact('label', 'value', 'sub', 'tone', 'url');

        if ($status) {
            $item['status'] = $status;
        }

        return $item;
    }

    /**
     * @return array{label: string, value: string, sub: string, tone: string, status: string}
     */
    private function systemStatusSummary(int $pendingApprovals, int $lowStock): array
    {
        $issues = $pendingApprovals + ($lowStock > 0 ? 1 : 0);

        return $this->summaryItem(
            'System Status',
            $issues > 0 ? 'Needs attention' : 'Operational',
            $issues > 0
                ? collect(array_filter([
                    $pendingApprovals > 0 ? "{$pendingApprovals} approvals" : null,
                    $lowStock > 0 ? "{$lowStock} low stock" : null,
                ]))->implode(' · ')
                : 'All services running',
            $issues > 0 ? 'amber' : 'green',
            null,
            $issues > 0 ? 'warn' : 'ok',
        );
    }

    /**
     * @return array{direction: string, text: string}
     */
    private function salesMonthTrend(?int $shopId): array
    {
        $thisMonth = $this->salesSnapshot($shopId, now()->startOfMonth(), now()->endOfDay());
        $lastMonth = $this->salesSnapshot(
            $shopId,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );

        if ($lastMonth['revenue'] <= 0) {
            return ['direction' => 'neutral', 'text' => 'No prior month baseline'];
        }

        $pct = (int) round((($thisMonth['revenue'] - $lastMonth['revenue']) / $lastMonth['revenue']) * 100);

        return [
            'direction' => $pct >= 0 ? 'up' : 'down',
            'text' => ($pct >= 0 ? '+' : '').$pct.'% vs last month',
        ];
    }

    private function greeting(User $user): string
    {
        $hour = (int) now()->format('G');

        $timeGreeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return "{$timeGreeting}, {$user->name}";
    }

    /**
     * @return array{count: int, revenue: float}
     */
    private function salesSnapshot(?int $shopId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from ??= today()->startOfDay();
        $to ??= today()->endOfDay();

        $query = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));

        return [
            'count' => (clone $query)->count(),
            'revenue' => (float) (clone $query)->sum('total'),
        ];
    }

    /**
     * @return array{labels: list<string>, counts: list<int>, revenue: list<float>}
     */
    private function salesMonthlyChart(?int $shopId, int $months = 6): array
    {
        $labels = [];
        $counts = [];
        $revenue = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $query = Sale::query()
                ->where('status', 'completed')
                ->whereYear('sold_at', $month->year)
                ->whereMonth('sold_at', $month->month)
                ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));

            $counts[] = (clone $query)->count();
            $revenue[] = (float) (clone $query)->sum('total');
        }

        return compact('labels', 'counts', 'revenue');
    }

    /**
     * @return array{labels: list<string>, counts: list<int>, colors: list<string>}
     */
    private function procurementPipelineChart(): array
    {
        $buckets = [
            'Quotation' => ['quotation_draft', 'draft'],
            'Order' => ['order_draft', 'cost_analysis', 'pending_approval'],
            'Approved' => ['approved', 'po_generated'],
            'In Transit' => ['in_transit'],
            'Received' => ['received'],
        ];

        $colors = ['#f59e0b', '#8b5cf6', '#22c55e', '#06b6d4', '#ff6b35'];
        $labels = [];
        $counts = [];

        foreach ($buckets as $label => $statuses) {
            $count = QuotationSeries::query()
                ->whereIn('status', $statuses)
                ->count();

            if ($count > 0) {
                $labels[] = $label;
                $counts[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'colors' => array_slice($colors, 0, count($labels)),
        ];
    }

    /**
     * @return array{labels: list<string>, revenue: list<float>}
     */
    private function revenueByShopChart(int $days = 30): array
    {
        $from = now()->subDays($days)->startOfDay();

        $rows = Sale::query()
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->where('sales.status', 'completed')
            ->where('sales.sold_at', '>=', $from)
            ->groupBy('shops.id', 'shops.name')
            ->orderByDesc(DB::raw('SUM(sales.total)'))
            ->limit(6)
            ->get([
                'shops.name as shop_name',
                DB::raw('SUM(sales.total) as revenue'),
            ]);

        return [
            'labels' => $rows->pluck('shop_name')->all(),
            'revenue' => $rows->pluck('revenue')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    private function vatOutstandingAmount(): float
    {
        return (float) TaxRemittance::query()
            ->where('period_year', now()->year)
            ->whereIn('status', ['open', 'filed'])
            ->get()
            ->sum(fn (TaxRemittance $r) => max(0, (float) $r->tax_collected - (float) $r->amount_remitted));
    }

    private function activeTransfers(int $limit)
    {
        return StockTransfer::query()
            ->with(['source', 'destination'])
            ->whereIn('status', ['pending', 'approved', 'dispatched', 'in_transit'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function globalLowStock(int $limit)
    {
        $aggregated = StockBalance::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity_on_hand) as total_on_hand')
            ->groupBy('product_id');

        return Product::query()
            ->joinSub($aggregated, 'stock_agg', fn ($join) => $join->on('products.id', '=', 'stock_agg.product_id'))
            ->where('products.reorder_level', '>', 0)
            ->whereColumn('stock_agg.total_on_hand', '<=', 'products.reorder_level')
            ->where('stock_agg.total_on_hand', '>', 0)
            ->orderBy('stock_agg.total_on_hand')
            ->limit($limit)
            ->get([
                'products.id',
                'products.part_number',
                'products.name',
                'products.reorder_level',
                'stock_agg.total_on_hand',
            ]);
    }

    private function recentOpenSeries(int $limit)
    {
        return QuotationSeries::query()
            ->with('supplier:id,name')
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->latest()
            ->limit($limit)
            ->get(['id', 'series_number', 'title', 'supplier_id', 'status', 'currency']);
    }

    private function inTransitCount(?User $user = null): int
    {
        $query = StockTransfer::query()->whereIn('status', ['dispatched', 'in_transit']);

        if ($user) {
            $query = $this->stockTransfers->scopeVisible($query, $user);
        }

        return $query->count();
    }

    /**
     * @return list<array{key: string, label: string, icon: string, count: int, url: string}>
     */
    private function buildApprovalPipeline(User $user): array
    {
        $counts = $this->approvals->pendingCountByModule($user);
        $steps = [];

        foreach (config('approvals.modules', []) as $key => $meta) {
            if (! ($meta['pipeline'] ?? true)) {
                continue;
            }

            if (! isset(config('approvals.module_models')[$key])) {
                continue;
            }

            $count = $counts[$key] ?? 0;

            if ($count === 0) {
                continue;
            }

            $steps[] = [
                'key' => $key,
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'count' => $count,
                'url' => route('approvals.index', ['module' => $key, 'filter' => 'mine']),
            ];
        }

        return $steps;
    }

    private function pendingApprovalsList(User $user, int $limit)
    {
        return Approval::query()
            ->pending()
            ->with(['requester', 'approvable'])
            ->when(
                ! $user->hasRole(config('approvals.default_approver_role')),
                fn ($q) => $q->forApprover($user)
            )
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function recentSales(?int $shopId, int $limit)
    {
        return Sale::query()
            ->where('status', 'completed')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->with(['shop:id,name', 'cashier:id,name'])
            ->latest('sold_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array{label: string, icon: string, url: string, variant: string, desc: string}>
     */
    private function quickActionsFor(User $user, string $role): array
    {
        $actions = [];

        if ($role === 'attendant' || $role === 'shop_manager') {
            if ($user->can('sales.create')) {
                $actions[] = ['label' => 'Cash Desk', 'icon' => 'fa-cash-register', 'url' => route('sales.desk'), 'variant' => 'primary', 'desc' => 'Checkout held orders'];
            }
            if ($user->can('sales.hold')) {
                $actions[] = ['label' => 'Order Entry', 'icon' => 'fa-cart-shopping', 'url' => route('sales.order'), 'variant' => 'secondary', 'desc' => 'Create new sale'];
            }
        }

        if ($role === 'shop_manager') {
            if ($user->can('transfer_requests.create')) {
                $actions[] = ['label' => 'Request Stock', 'icon' => 'fa-inbox', 'url' => route('transfer-requests.create'), 'variant' => 'secondary', 'desc' => 'Request from warehouse'];
            }
            if ($user->can('inventory.view')) {
                $actions[] = ['label' => 'Inventory', 'icon' => 'fa-boxes-stacked', 'url' => route('inventory.index'), 'variant' => 'secondary', 'desc' => 'Stock levels'];
            }
            if ($user->can('reports.view')) {
                $actions[] = ['label' => 'Reports', 'icon' => 'fa-chart-line', 'url' => route('reports.index'), 'variant' => 'secondary', 'desc' => 'Sales & stock insights'];
            }
        }

        if ($role === 'warehouse') {
            if ($user->can('transfers.create')) {
                $actions[] = ['label' => 'Stock Transfer', 'icon' => 'fa-right-left', 'url' => route('stock-transfers.create'), 'variant' => 'primary', 'desc' => 'Move stock between locations'];
            }
            if ($user->can('procurement.manage')) {
                $actions[] = ['label' => 'Purchase Orders', 'icon' => 'fa-truck-ramp-box', 'url' => route('purchase-orders.index'), 'variant' => 'secondary', 'desc' => 'Receive incoming stock'];
            }
            if ($user->can('inventory.adjust')) {
                $actions[] = ['label' => 'Stock Adjustment', 'icon' => 'fa-sliders', 'url' => route('stock-adjustments.create'), 'variant' => 'secondary', 'desc' => 'Correct stock levels'];
            }
            if ($user->can('procurement.view')) {
                $actions[] = ['label' => 'Quotation Series', 'icon' => 'fa-folder-open', 'url' => route('quotation-series.index'), 'variant' => 'secondary', 'desc' => 'Procurement pipeline'];
            }
        }

        if ($role === 'admin') {
            if ($user->can('approvals.act')) {
                $actions[] = ['label' => 'Approvals', 'icon' => 'fa-inbox', 'url' => route('approvals.index'), 'variant' => 'primary', 'desc' => 'Review pending requests'];
            }
            if ($user->can('sales.view')) {
                $actions[] = ['label' => 'Sales', 'icon' => 'fa-receipt', 'url' => route('sales.index'), 'variant' => 'secondary', 'desc' => 'All transactions'];
            }
            if ($user->can('inventory.view')) {
                $actions[] = ['label' => 'Inventory', 'icon' => 'fa-boxes-stacked', 'url' => route('inventory.index'), 'variant' => 'secondary', 'desc' => 'Global stock'];
            }
            if ($user->can('reports.view')) {
                $actions[] = ['label' => 'Reports', 'icon' => 'fa-chart-line', 'url' => route('reports.index'), 'variant' => 'secondary', 'desc' => 'Business insights'];
            }
            if ($user->can('finance.view')) {
                $actions[] = ['label' => 'Finance', 'icon' => 'fa-coins', 'url' => route('journal-entries.index'), 'variant' => 'secondary', 'desc' => 'Journal & GL'];
            }
        }

        if ($user->can('payroll.view')) {
            $actions[] = ['label' => 'Payroll', 'icon' => 'fa-file-invoice', 'url' => route('payroll.index'), 'variant' => 'secondary', 'desc' => 'Payroll periods & payslips'];
        }

        return $actions;
    }
}
