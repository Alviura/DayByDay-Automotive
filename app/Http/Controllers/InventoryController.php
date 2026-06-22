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
        $products = $this->inventory->paginatedProductStockSummary($request);
        $stats = $this->inventory->indexStats();

        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name', 'code']);
        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('inventory.index', compact('products', 'stats', 'warehouses', 'shops'));
    }

    public function show(Product $product): View
    {
        $product->load(['unit', 'category']);

        $context = $this->inventory->productShowContext($product);

        return view('inventory.show', array_merge(
            compact('product'),
            $context
        ));
    }

    public function movements(Request $request): View
    {
        $movements = StockLedger::query()
            ->with(['product.unit', 'location', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($inner) use ($term) {
                    $inner->where('reference_number', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%")
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$term}%")
                            ->orWhere('part_number', 'like', "%{$term}%"));
                });
            })
            ->when($request->transaction_type, fn ($q) => $q->where('transaction_type', $request->transaction_type))
            ->when($request->location_type && $request->location_id, function ($q) use ($request) {
                $morph = $request->location_type === 'warehouse' ? Warehouse::class : Shop::class;
                $q->where('location_type', $morph)->where('location_id', $request->location_id);
            })
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(25)
            ->withQueryString();

        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name', 'code']);
        $shops = Shop::active()->orderBy('name')->get(['id', 'name', 'code']);

        $transactionTypes = array_keys(StockLedger::TYPE_LABELS);

        $typeSummary = StockLedger::query()
            ->selectRaw('transaction_type, COUNT(*) as entries, SUM(quantity) as net_qty, SUM(ABS(quantity) * COALESCE(unit_cost, 0)) as total_value')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('transaction_type')
            ->orderByDesc('entries')
            ->get();

        return view('inventory.movements', compact(
            'movements', 'warehouses', 'shops', 'transactionTypes', 'typeSummary'
        ));
    }

    public function valuation(Request $request): View
    {
        $locations = $this->inventory->valuationForAllLocations()
            ->sortByDesc('total_value')
            ->values();

        if ($request->location_type && $request->location_id) {
            $model = $request->location_type === 'warehouse'
                ? Warehouse::find($request->location_id)
                : Shop::find($request->location_id);

            $detail = $model ? $this->inventory->valuation($model) : null;
            $detailLocation = $model;

            if ($detail) {
                $detail['balances'] = $detail['balances']
                    ->sortByDesc(fn (StockBalance $b) => $b->stockValue())
                    ->values();
            }
        } else {
            $detail = null;
            $detailLocation = null;
        }

        $grandTotal = (float) $locations->sum('total_value');
        $grandUnits = (float) $locations->sum('total_units');
        $warehouseTotal = (float) $locations->where('type', 'Warehouse')->sum('total_value');
        $shopTotal = (float) $locations->where('type', 'Shop')->sum('total_value');
        $uniqueSkus = (int) StockBalance::query()
            ->where('quantity_on_hand', '>', 0)
            ->distinct('product_id')
            ->count('product_id');
        $activeLocations = $locations->where('total_value', '>', 0)->count();

        return view('inventory.valuation', compact(
            'locations',
            'detail',
            'detailLocation',
            'grandTotal',
            'grandUnits',
            'warehouseTotal',
            'shopTotal',
            'uniqueSkus',
            'activeLocations',
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
