<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
        $this->middleware('permission:inventory.view');
    }

    public function index(Request $request): View
    {
        $balances = StockBalance::query()
            ->with(['product.unit', 'location'])
            ->search($request->search)
            ->when($request->location_type && $request->location_id, function ($q) use ($request) {
                $morph = $request->location_type === 'warehouse' ? Warehouse::class : Shop::class;
                $q->where('location_type', $morph)->where('location_id', $request->location_id);
            })
            ->when($request->filter === 'low_stock', function ($q) {
                $q->whereHas('product', fn ($pq) => $pq->where('reorder_level', '>', 0))
                    ->whereRaw('stock_balances.quantity_on_hand <= (SELECT reorder_level FROM products WHERE products.id = stock_balances.product_id)');
            })
            ->when($request->filter === 'in_stock', fn ($q) => $q->where('quantity_on_hand', '>', 0))
            ->when($request->filter === 'out_of_stock', fn ($q) => $q->where('quantity_on_hand', '<=', 0))
            ->when($request->sort === 'product', fn ($q) => $q->orderBy(
                Product::select('name')->whereColumn('products.id', 'stock_balances.product_id')
            ))
            ->when($request->sort === 'qty', fn ($q) => $q->orderByDesc('quantity_on_hand'))
            ->when($request->sort === 'value', fn ($q) => $q->orderByRaw('(quantity_on_hand * average_cost) DESC'))
            ->when(! in_array($request->sort, ['product', 'qty', 'value'], true), fn ($q) => $q->latest())
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'skus' => StockBalance::where('quantity_on_hand', '>', 0)->count(),
            'units' => (float) StockBalance::sum('quantity_on_hand'),
            'reserved' => (float) StockBalance::sum('quantity_reserved'),
            'value' => StockBalance::selectRaw('SUM(quantity_on_hand * average_cost) as total')->value('total') ?? 0,
        ];

        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name', 'code']);
        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('inventory.index', compact('balances', 'stats', 'warehouses', 'shops'));
    }

    public function show(Product $product): View
    {
        $balances = StockBalance::query()
            ->with('location')
            ->where('product_id', $product->id)
            ->orderByDesc('quantity_on_hand')
            ->get();

        $movements = StockLedger::query()
            ->with(['location', 'user'])
            ->where('product_id', $product->id)
            ->latest()
            ->limit(25)
            ->get();

        $product->load(['unit', 'category']);

        return view('inventory.show', compact('product', 'balances', 'movements'));
    }

    public function movements(Request $request): View
    {
        $movements = StockLedger::query()
            ->with(['product', 'location', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('reference_number', 'like', "%{$request->search}%")
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$request->search}%")
                            ->orWhere('part_number', 'like', "%{$request->search}%"));
                });
            })
            ->when($request->transaction_type, fn ($q) => $q->where('transaction_type', $request->transaction_type))
            ->when($request->location_type && $request->location_id, function ($q) use ($request) {
                $morph = $request->location_type === 'warehouse' ? Warehouse::class : Shop::class;
                $q->where('location_type', $morph)->where('location_id', $request->location_id);
            })
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(25)
            ->withQueryString();

        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name', 'code']);
        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        $transactionTypes = [
            'opening_balance', 'purchase_receipt', 'transfer_out', 'transfer_in',
            'sale', 'customer_return', 'supplier_return', 'adjustment',
        ];

        return view('inventory.movements', compact('movements', 'warehouses', 'shops', 'transactionTypes'));
    }

    public function valuation(Request $request): View
    {
        $locations = $this->inventory->valuationForAllLocations();

        if ($request->location_type && $request->location_id) {
            $model = $request->location_type === 'warehouse'
                ? Warehouse::find($request->location_id)
                : Shop::find($request->location_id);

            $detail = $model ? $this->inventory->valuation($model) : null;
        } else {
            $detail = null;
        }

        $grandTotal = $locations->sum('total_value');
        $grandUnits = $locations->sum('total_units');
        $grandSkus = $locations->sum('sku_count');

        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name', 'code']);
        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('inventory.valuation', compact(
            'locations', 'detail', 'grandTotal', 'grandUnits', 'grandSkus', 'warehouses', 'shops'
        ));
    }

    public function balanceLookup(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'location_type' => ['required', 'in:warehouse,shop'],
            'location_id' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $location = $request->location_type === 'warehouse'
            ? Warehouse::findOrFail($request->location_id)
            : Shop::findOrFail($request->location_id);

        $balance = $this->inventory->getBalance($product, $location);

        return response()->json([
            'system_quantity' => (float) ($balance?->quantity_on_hand ?? 0),
            'unit_cost' => (float) ($balance?->average_cost ?? $product->cost_price ?? 0),
            'available' => $this->inventory->available($product, $location, $balance),
        ]);
    }
}
