<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Http\Requests\StoreSupplierReturnRequest;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierReturnController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
        $this->middleware('permission:returns.view')->only(['index', 'show', 'availability']);
        $this->middleware('permission:returns.create')->only(['create', 'store', 'submit', 'destroy']);
    }

    public function index(Request $request): View
    {
        $returns = ReturnRecord::query()
            ->where('type', 'supplier')
            ->with(['supplier', 'warehouse', 'items'])
            ->withCount('items')
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($inner) use ($term) {
                    $inner->where('return_number', 'like', "%{$term}%")
                        ->orWhere('reason', 'like', "%{$term}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ReturnRecord::where('type', 'supplier')->count(),
            'draft' => ReturnRecord::where('type', 'supplier')->where('status', 'draft')->count(),
            'pending' => ReturnRecord::where('type', 'supplier')->where('status', 'pending')->count(),
            'completed' => ReturnRecord::where('type', 'supplier')->where('status', 'completed')->count(),
            'rejected' => ReturnRecord::where('type', 'supplier')->where('status', 'rejected')->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'count' => $stats['draft']],
            ['key' => 'pending', 'label' => 'Awaiting Approval', 'icon' => 'fa-hourglass-half', 'count' => $stats['pending']],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => 'fa-circle-check', 'count' => $stats['completed']],
            ['key' => 'rejected', 'label' => 'Rejected', 'icon' => 'fa-circle-xmark', 'count' => $stats['rejected']],
        ];

        return view('returns.supplier.index', compact('returns', 'stats', 'pipeline'));
    }

    public function create(Request $request): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'unit_id']);

        return view('returns.supplier.create', [
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'prefillWarehouseId' => $request->integer('warehouse_id') ?: null,
            'prefillProductId' => $request->integer('product_id') ?: null,
        ]);
    }

    public function store(StoreSupplierReturnRequest $request): RedirectResponse
    {
        $return = ReturnRecord::create([
            'return_number' => ReturnRecord::generateNumber('supplier'),
            'type' => 'supplier',
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'reason' => $request->reason,
            'status' => 'draft',
        ]);

        foreach ($request->items as $line) {
            $return->items()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'condition' => $line['condition'],
                'restock' => false,
                'replacement' => false,
            ]);
        }

        return redirect()->route('supplier-returns.show', $return)
            ->with('status', 'Supplier return draft created. Review and submit for approval.');
    }

    public function show(ReturnRecord $supplierReturn): View
    {
        if ($supplierReturn->type !== 'supplier') {
            abort(404);
        }

        $supplierReturn->load([
            'supplier', 'warehouse', 'items.product.unit',
            'approver', 'processor', 'approval.actions.actor',
        ]);

        return view('returns.supplier.show', ['return' => $supplierReturn]);
    }

    public function submit(ReturnRecord $supplierReturn): RedirectResponse
    {
        if ($supplierReturn->type !== 'supplier' || ! $supplierReturn->canSubmit()) {
            return back()->with('error', 'This return cannot be submitted.');
        }

        try {
            $supplierReturn->requestApproval($supplierReturn->reason);
            $supplierReturn->update(['status' => 'pending']);

            return back()->with('status', 'Return submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(ReturnRecord $supplierReturn): RedirectResponse
    {
        if ($supplierReturn->type !== 'supplier' || ! $supplierReturn->canDelete()) {
            return back()->with('error', 'This return cannot be deleted.');
        }

        $supplierReturn->delete();

        return redirect()->route('supplier-returns.index')
            ->with('status', 'Return deleted.');
    }

    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $product = Product::findOrFail($request->product_id);

        return response()->json([
            'available' => $this->inventory->available($product, $warehouse),
        ]);
    }
}
