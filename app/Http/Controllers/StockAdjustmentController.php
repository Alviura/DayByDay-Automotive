<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\WarehouseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private InventoryService $inventory,
        private WarehouseAccessService $warehouseAccess,
    ) {
        $this->middleware('permission:inventory.view')->only(['index', 'show']);
        $this->middleware('permission:inventory.adjust')->only(['create', 'store', 'submit', 'destroy']);
    }

    public function index(Request $request): View
    {
        $scopedWarehouseId = $this->warehouseAccess->scopedWarehouseId(auth()->user());
        $baseQuery = StockAdjustment::query();

        if ($scopedWarehouseId) {
            $this->warehouseAccess->scopeWarehouseMorph($baseQuery, 'location_type', 'location_id', $scopedWarehouseId);
        }

        $adjustments = (clone $baseQuery)
            ->with(['location', 'creator', 'items'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('adjustment_number', 'like', "%{$request->search}%")
                    ->orWhere('notes', 'like', "%{$request->search}%");
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->reason, fn ($q) => $q->where('reason', $request->reason))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
        ];

        return view('stock-adjustments.index', compact('adjustments', 'stats'));
    }

    public function create(): View
    {
        $scopedWarehouseId = $this->warehouseAccess->scopedWarehouseId(auth()->user());
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $shops = $scopedWarehouseId ? collect() : Shop::active()->orderBy('name')->get();
        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'cost_price', 'unit_id']);
        $lockedWarehouse = $scopedWarehouseId ? $warehouses->firstWhere('id', $scopedWarehouseId) : null;

        return view('stock-adjustments.create', compact('warehouses', 'shops', 'products', 'lockedWarehouse'));
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $location = $request->locationModel();

        $adjustment = StockAdjustment::create([
            'adjustment_number' => StockAdjustment::generateNumber(),
            'location_type' => $location->getMorphClass(),
            'location_id' => $location->getKey(),
            'reason' => $request->reason,
            'status' => 'draft',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $balance = $this->inventory->getBalance($product, $location);
            $systemQty = (float) ($balance?->quantity_on_hand ?? 0);
            $unitCost = (float) ($balance?->average_cost ?? $product->cost_price ?? 0);

            $adjustment->items()->create([
                'product_id' => $product->id,
                'system_quantity' => $systemQty,
                'counted_quantity' => $item['counted_quantity'],
                'difference' => $item['counted_quantity'] - $systemQty,
                'unit_cost' => $unitCost,
            ]);
        }

        return redirect()
            ->route('stock-adjustments.show', $adjustment)
            ->with('status', 'Stock adjustment draft created. Review and submit for approval.');
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load(['location', 'creator', 'approver', 'items.product.unit', 'approval']);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    public function submit(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if (! $stockAdjustment->canSubmit()) {
            return back()->with('error', 'This adjustment cannot be submitted for approval.');
        }

        if ($stockAdjustment->items()->where('difference', '!=', 0)->doesntExist()) {
            return back()->with('error', 'At least one line must have a quantity difference before submitting.');
        }

        try {
            $stockAdjustment->requestApproval($stockAdjustment->notes);
            $stockAdjustment->update(['status' => 'pending']);

            return redirect()
                ->route('stock-adjustments.show', $stockAdjustment)
                ->with('status', 'Adjustment submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return back()->with('error', 'Only draft adjustments can be deleted.');
        }

        $stockAdjustment->delete();

        return redirect()
            ->route('stock-adjustments.index')
            ->with('status', 'Draft adjustment deleted.');
    }
}
