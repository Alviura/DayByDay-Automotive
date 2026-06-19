<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Http\Requests\StoreSupplierReturnRequest;
use App\Models\ReturnRecord;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:returns.view')->only(['index', 'show']);
        $this->middleware('permission:returns.create')->only(['create', 'store', 'submit', 'destroy']);
    }

    public function index(Request $request): View
    {
        $returns = ReturnRecord::query()
            ->where('type', 'supplier')
            ->with(['supplier', 'warehouse', 'items'])
            ->when($request->search, fn ($q) => $q->where('return_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ReturnRecord::where('type', 'supplier')->count(),
            'pending' => ReturnRecord::where('type', 'supplier')->where('status', 'pending')->count(),
            'completed' => ReturnRecord::where('type', 'supplier')->where('status', 'completed')->count(),
        ];

        return view('returns.supplier.index', compact('returns', 'stats'));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = \App\Models\Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'unit_id']);

        return view('returns.supplier.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(StoreSupplierReturnRequest $request): RedirectResponse
    {
        $return = ReturnRecord::create([
            'return_number' => ReturnRecord::generateNumber('supplier'),
            'type' => 'supplier',
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'reason' => $request->reason,
            'status' => 'pending',
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
            ->with('status', 'Supplier return created. Submit for approval when ready.');
    }

    public function show(ReturnRecord $supplierReturn): View
    {
        if ($supplierReturn->type !== 'supplier') {
            abort(404);
        }

        $supplierReturn->load([
            'supplier', 'warehouse', 'items.product.unit',
            'approver', 'processor', 'approval',
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
}
