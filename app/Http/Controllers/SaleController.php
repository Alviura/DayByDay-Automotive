<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Http\Requests\CheckoutSaleRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\StoreSaleRequest;
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
        $this->middleware('permission:sales.create')->only(['pos', 'searchProducts', 'complete', 'checkout']);
        $this->middleware('permission:sales.hold')->only(['hold', 'abandon']);
        $this->middleware('permission:sales.reverse')->only(['reverse']);
    }

    public function index(Request $request): View
    {
        $query = Sale::query()
            ->with(['shop', 'cashier', 'items'])
            ->when($request->search, fn ($q) => $q->where('receipt_number', 'like', "%{$request->search}%")
                ->orWhere('customer_name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->shop_id, fn ($q) => $q->where('shop_id', $request->shop_id));

        if ($shopId = $this->scopedShopId()) {
            $query->where('shop_id', $shopId);
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        $statsQuery = Sale::query();
        if ($shopId = $this->scopedShopId()) {
            $statsQuery->where('shop_id', $shopId);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'held' => (clone $statsQuery)->where('status', 'held')->count(),
            'today_total' => (clone $statsQuery)
                ->where('status', 'completed')
                ->whereDate('sold_at', today())
                ->sum('total'),
        ];

        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('sales.index', compact('sales', 'stats', 'shops'));
    }

    public function pos(Request $request): View
    {
        $shops = Shop::active()->orderBy('name')->get();
        $shopId = $this->resolveShopId($request);
        $shop = Shop::findOrFail($shopId);

        $heldSales = Sale::query()
            ->where('shop_id', $shop->id)
            ->where('status', 'held')
            ->with('items')
            ->latest()
            ->limit(10)
            ->get();

        $resumeSale = null;
        if ($request->sale) {
            $resumeSale = Sale::with(['items.product.unit'])
                ->where('shop_id', $shop->id)
                ->where('status', 'held')
                ->find($request->sale);
        }

        $paymentMethods = \App\Models\Payment::methods();
        $taxRate = config('sales.tax_rate', 0);

        return view('sales.pos', compact('shops', 'shop', 'heldSales', 'resumeSale', 'paymentMethods', 'taxRate'));
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

        $sale->load(['shop', 'cashier', 'items.product.unit', 'payments.receiver', 'reverser']);

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
                $request->notes
            );

            return redirect()->route('sales.pos', ['shop_id' => $shop->id, 'sale' => $sale->id])
                ->with('status', 'Sale held as '.$sale->receipt_number);
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
                $request->notes
            );

            $sale = $this->sales->complete($sale, $request->payments);

            return redirect()->route('receipts.show', $sale)
                ->with('status', 'Sale completed — '.$sale->receipt_number);
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
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

            return redirect()->route('sales.pos', ['shop_id' => $shopId])
                ->with('status', 'Held sale discarded.');
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

        if ($user->hasRole('Shop Manager') && $user->shop_id) {
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
