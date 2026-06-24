<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Http\Requests\CheckoutSaleRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\CustomerAccount;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Services\InventoryService;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private SaleService $sales,
        private InventoryService $inventory
    ) {
        $this->middleware('permission:sales.view')->only(['index', 'show']);
        $this->middleware('permission:sales.hold')->only(['order', 'searchProducts', 'hold']);
        $this->middleware('permission:sales.create')->only(['desk', 'deskCheckout', 'complete', 'checkout', 'pos', 'issueOnAccount']);
        $this->middleware('permission:sales.hold')->only(['abandon']);
        $this->middleware('permission:sales.reverse')->only(['reverse']);
    }

    public function index(Request $request): View
    {
        $baseQuery = Sale::query();
        if ($shopId = $this->scopedShopId()) {
            $baseQuery->where('shop_id', $shopId);
        }

        $query = (clone $baseQuery)
            ->with(['shop', 'orderedBy', 'completedBy', 'customerAccount', 'items'])
            ->withCount('items')
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($inner) use ($term) {
                    $inner->where('receipt_number', 'like', "%{$term}%")
                        ->orWhere('customer_name', 'like', "%{$term}%")
                        ->orWhere('customer_phone', 'like', "%{$term}%");
                });
            })
            ->when($request->sale_type, fn ($q) => $q->where('sale_type', $request->sale_type))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->shop_id, fn ($q) => $q->where('shop_id', $request->shop_id))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort === 'total', fn ($q) => $q->orderByDesc('total'))
            ->when(! in_array($request->sort, ['oldest', 'total'], true), fn ($q) => $q->latest());

        $sales = $query->paginate(15)->withQueryString();

        $statsQuery = clone $baseQuery;

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'held' => (clone $statsQuery)->where('status', 'held')->count(),
            'reversed' => (clone $statsQuery)->where('status', 'reversed')->count(),
            'today_total' => (float) (clone $statsQuery)
                ->where('status', 'completed')
                ->whereDate('sold_at', today())
                ->sum('total'),
            'today_count' => (clone $statsQuery)
                ->where('status', 'completed')
                ->whereDate('sold_at', today())
                ->count(),
            'month_total' => (float) (clone $statsQuery)
                ->where('status', 'completed')
                ->whereMonth('sold_at', now()->month)
                ->whereYear('sold_at', now()->year)
                ->sum('total'),
            'month_count' => (clone $statsQuery)
                ->where('status', 'completed')
                ->whereMonth('sold_at', now()->month)
                ->whereYear('sold_at', now()->year)
                ->count(),
        ];

        $stats['avg_ticket_today'] = $stats['today_count'] > 0
            ? round($stats['today_total'] / $stats['today_count'], 2)
            : 0;

        $statusBreakdown = (clone $statsQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'label' => (new Sale(['status' => $row->status]))->statusLabel(),
                'count' => (int) $row->count,
            ]);

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'held', 'label' => 'At Desk', 'icon' => 'fa-hourglass-half', 'count' => $stats['held']],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => 'fa-circle-check', 'count' => $stats['completed']],
            ['key' => 'reversed', 'label' => 'Reversed', 'icon' => 'fa-rotate-left', 'count' => $stats['reversed']],
        ];

        $chartData = [
            'monthly' => ['labels' => [], 'counts' => [], 'revenue' => []],
            'status' => [
                'labels' => ['At Cash Desk', 'Completed', 'Reversed'],
                'counts' => [$stats['held'], $stats['completed'], $stats['reversed']],
            ],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartData['monthly']['labels'][] = $month->format('M Y');
            $chartData['monthly']['counts'][] = (clone $statsQuery)
                ->where('status', 'completed')
                ->whereYear('sold_at', $month->year)
                ->whereMonth('sold_at', $month->month)
                ->count();
            $chartData['monthly']['revenue'][] = (float) (clone $statsQuery)
                ->where('status', 'completed')
                ->whereYear('sold_at', $month->year)
                ->whereMonth('sold_at', $month->month)
                ->sum('total');
        }

        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('sales.index', compact('sales', 'stats', 'shops', 'pipeline', 'chartData', 'statusBreakdown'));
    }

    public function pos(Request $request): RedirectResponse|View
    {
        if ($request->user()->can('sales.create') && ! $request->user()->can('sales.hold')) {
            return redirect()->route('sales.desk', $request->only('shop_id'));
        }

        if ($request->user()->can('sales.hold') && ! $request->user()->can('sales.create')) {
            return redirect()->route('sales.order', $request->only('shop_id'));
        }

        return redirect()->route('sales.desk', $request->only('shop_id'));
    }

    public function order(Request $request): View
    {
        $shops = Shop::active()->orderBy('name')->get();
        $shopId = $this->resolveShopId($request);
        $shop = Shop::findOrFail($shopId);
        $creditAccounts = CustomerAccount::active()->orderBy('name')->get(['id', 'name', 'contact_name']);

        return view('sales.order', compact('shops', 'shop', 'creditAccounts'));
    }

    public function desk(Request $request): View
    {
        $shopId = $this->resolveShopId($request);
        $shop = Shop::findOrFail($shopId);

        $queue = Sale::query()
            ->with(['orderedBy', 'items', 'customerAccount'])
            ->withCount('items')
            ->where('shop_id', $shop->id)
            ->where('status', 'held')
            ->latest('submitted_at')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $completedTodayQuery = Sale::where('shop_id', $shop->id)
            ->where('status', 'completed')
            ->whereDate('sold_at', today());

        $stats = [
            'waiting' => Sale::where('shop_id', $shop->id)->where('status', 'held')->count(),
            'completed_today' => (clone $completedTodayQuery)->count(),
            'today_total' => (float) (clone $completedTodayQuery)->sum('total'),
            'avg_ticket_today' => 0,
            'oldest_wait_mins' => 0,
        ];

        $stats['avg_ticket_today'] = $stats['completed_today'] > 0
            ? round($stats['today_total'] / $stats['completed_today'], 2)
            : 0;

        $oldestHeld = Sale::where('shop_id', $shop->id)
            ->where('status', 'held')
            ->orderByRaw('COALESCE(submitted_at, created_at) asc')
            ->first();

        if ($oldestHeld) {
            $stats['oldest_wait_mins'] = ($oldestHeld->submitted_at ?? $oldestHeld->created_at)->diffInMinutes(now());
        }

        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('sales.desk', compact('shop', 'queue', 'stats', 'shops'));
    }

    public function deskCheckout(Request $request, Sale $sale): View|RedirectResponse
    {
        if ($redirect = $this->authorizeShopAccess($sale)) {
            return $redirect;
        }

        if (! $sale->canComplete()) {
            return redirect()->route('sales.desk', ['shop_id' => $sale->shop_id])
                ->with('error', 'This order is no longer awaiting checkout.');
        }

        $sale->load(['items.product.unit', 'orderedBy', 'shop', 'customerAccount']);

        $paymentMethods = \App\Models\Payment::methods();
        $taxRate = config('sales.tax_rate', 0);

        return view('sales.desk-checkout', compact('sale', 'paymentMethods', 'taxRate'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $shop = Shop::findOrFail($this->resolveShopId($request));

        $products = Product::query()
            ->active()
            ->search($request->q)
            ->with('unit:id,name,abbreviation')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'part_number', 'name', 'min_selling_price', 'max_selling_price', 'unit_id']);

        return response()->json(
            $products->map(fn (Product $product) => [
                'id' => $product->id,
                'part_number' => $product->part_number,
                'name' => $product->name,
                'min_selling_price' => (float) $product->min_selling_price,
                'max_selling_price' => (float) $product->max_selling_price,
                'available' => $this->inventory->available($product, $shop),
                'unit' => $product->unit?->abbreviation,
            ])
        );
    }

    public function show(Sale $sale): View|RedirectResponse
    {
        if ($redirect = $this->authorizeShopAccess($sale)) {
            return $redirect;
        }

        $sale->load([
            'shop', 'cashier', 'orderedBy', 'completedBy', 'customerAccount', 'customerInvoice',
            'items.product.unit', 'payments.receiver', 'payments.shop', 'reverser',
            'returnRecords' => fn ($q) => $q->withCount('items')->latest(),
        ]);

        return view('sales.show', compact('sale'));
    }

    public function hold(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $shop = Shop::findOrFail($this->resolveShopId($request));
            $existing = $request->sale_id ? Sale::find($request->sale_id) : null;

            if ($existing && $redirect = $this->authorizeShopAccess($existing)) {
                return $redirect;
            }

            $sale = $this->sales->hold(
                $shop,
                $request->items,
                auth()->user(),
                $existing,
                $request->customer_name,
                $request->customer_phone,
                $request->notes,
                $request->input('sale_type', 'retail'),
                $request->customer_account_id ? (int) $request->customer_account_id : null,
                $request->vehicle_plate
            );

            if ($request->user()->can('sales.create') && $existing) {
                return redirect()->route('sales.desk.checkout', $sale)
                    ->with('status', 'Order updated.');
            }

            $message = $sale->isCredit()
                ? 'Credit order sent to cash desk — '.$sale->receipt_number
                : 'Order sent to cash desk — '.$sale->receipt_number;

            return redirect()->route('sales.order', ['shop_id' => $shop->id])
                ->with('status', $message);
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function checkout(CheckoutSaleRequest $request): RedirectResponse
    {
        try {
            $shop = Shop::findOrFail($this->resolveShopId($request));
            $existing = $request->sale_id ? Sale::find($request->sale_id) : null;

            if ($existing && $redirect = $this->authorizeShopAccess($existing)) {
                return $redirect;
            }

            $sale = $this->sales->hold(
                $shop,
                $request->items,
                auth()->user(),
                $existing,
                $request->customer_name,
                $request->customer_phone,
                $request->notes,
                $existing?->sale_type ?? 'retail',
                $existing?->customer_account_id,
                $existing?->vehicle_plate
            );

            if ($sale->isCredit()) {
                return back()->with('error', 'Credit sales must be issued on account, not paid at checkout.');
            }

            $sale = $this->sales->complete($sale, $request->payments);

            return redirect()->route('receipts.show', $sale)
                ->with('status', 'Sale completed — '.$sale->receipt_number);
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function issueOnAccount(Sale $sale): RedirectResponse
    {
        if ($redirect = $this->authorizeShopAccess($sale)) {
            return $redirect;
        }

        try {
            $sale = $this->sales->completeOnAccount($sale);

            return redirect()->route('sales.show', $sale)
                ->with('status', 'Parts issued on account — '.$sale->receipt_number.'. Payment due on monthly invoice.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(PaymentRequest $request, Sale $sale): RedirectResponse
    {
        if ($redirect = $this->authorizeShopAccess($sale)) {
            return $redirect;
        }

        try {
            $sale = $this->sales->complete($sale, $request->payments);

            return redirect()->route('receipts.show', $sale)
                ->with('status', 'Sale completed — '.$sale->receipt_number);
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function abandon(Sale $sale): RedirectResponse
    {
        if ($redirect = $this->authorizeShopAccess($sale)) {
            return $redirect;
        }

        try {
            $shopId = $sale->shop_id;
            $this->sales->abandonHeld($sale);

            $route = auth()->user()->can('sales.create')
                ? route('sales.desk', ['shop_id' => $shopId])
                : route('sales.order', ['shop_id' => $shopId]);

            return redirect($route)->with('status', 'Order discarded and stock released.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reverse(Request $request, Sale $sale): RedirectResponse
    {
        if ($redirect = $this->authorizeShopAccess($sale)) {
            return $redirect;
        }

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        try {
            $this->sales->reverse($sale, auth()->user(), $request->reason);

            return redirect()->route('sales.show', $sale)
                ->with('status', 'Sale reversed and stock restored.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function resolveShopId(Request $request): int
    {
        if ($request->filled('shop_id')) {
            return (int) $request->shop_id;
        }

        if ($shopId = $this->scopedShopId()) {
            return $shopId;
        }

        return (int) Shop::active()->orderBy('id')->value('id');
    }

    private function scopedShopId(): ?int
    {
        $user = auth()->user();

        if ($user->shop_id && $user->hasAnyRole(['Shop Manager', 'Shop Attendant'])) {
            return (int) $user->shop_id;
        }

        return null;
    }

    private function authorizeShopAccess(Sale $sale): ?RedirectResponse
    {
        $scoped = $this->scopedShopId();

        if ($scoped && (int) $sale->shop_id !== $scoped) {
            return redirect()->route('sales.index')->with('error', 'You cannot access sales for this shop.');
        }

        return null;
    }
}
