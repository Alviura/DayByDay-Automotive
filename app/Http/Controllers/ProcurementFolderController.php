<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Http\Requests\ProcurementItemRequest;
use App\Http\Requests\RunCostAnalysisRequest;
use App\Http\Requests\StoreProcurementFolderRequest;
use App\Http\Requests\UpdateProcurementFolderRequest;
use App\Models\ProcurementFolder;
use App\Models\ProcurementItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\CostAnalysisService;
use App\Services\ProcurementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementFolderController extends Controller
{
    public function __construct(
        private CostAnalysisService $costAnalysis,
        private ProcurementService $procurement
    ) {
        $this->middleware('permission:procurement.view')->only(['index', 'show']);
        $this->middleware('permission:procurement.manage')->only([
            'create', 'store', 'edit', 'update', 'destroy',
            'storeItem', 'destroyItem', 'runCostAnalysis', 'submit', 'generatePo', 'markInTransit', 'close',
        ]);
    }

    public function index(Request $request): View
    {
        $folders = ProcurementFolder::query()
            ->with(['supplier', 'creator'])
            ->withCount('items')
            ->when($request->search, fn ($q) => $q->where('folder_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ProcurementFolder::count(),
            'draft' => ProcurementFolder::where('status', 'draft')->count(),
            'pending' => ProcurementFolder::where('status', 'pending_approval')->count(),
            'in_transit' => ProcurementFolder::where('status', 'in_transit')->count(),
        ];

        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);

        return view('procurement.folders.index', compact('folders', 'stats', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('procurement.folders.create', compact('suppliers'));
    }

    public function store(StoreProcurementFolderRequest $request): RedirectResponse
    {
        $folder = ProcurementFolder::create([
            'folder_number' => ProcurementFolder::generateNumber(),
            'supplier_id' => $request->supplier_id,
            'currency' => strtoupper($request->currency),
            'exchange_rate' => $request->exchange_rate ?? 1,
            'import_type' => $request->import_type,
            'status' => 'draft',
            'notes' => $request->notes,
            'total_freight' => $request->total_freight ?? 0,
            'total_tax' => $request->total_tax ?? 0,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('procurement.folders.show', $folder)
            ->with('status', 'Procurement folder created. Add line items next.');
    }

    public function show(ProcurementFolder $folder): View
    {
        $folder->load([
            'supplier', 'creator', 'approver', 'items.product.unit',
            'purchaseOrders.items', 'goodsReceiptNotes.warehouse', 'approval',
        ]);
        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'cost_price', 'unit_id']);

        return view('procurement.folders.show', compact('folder', 'products'));
    }

    public function edit(ProcurementFolder $folder): View|RedirectResponse
    {
        if (! $folder->canEdit()) {
            return redirect()->route('procurement.folders.show', $folder)
                ->with('error', 'This folder cannot be edited.');
        }

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('procurement.folders.edit', compact('folder', 'suppliers'));
    }

    public function update(UpdateProcurementFolderRequest $request, ProcurementFolder $folder): RedirectResponse
    {
        if (! $folder->canEdit()) {
            return back()->with('error', 'This folder cannot be edited.');
        }

        $folder->update([
            'supplier_id' => $request->supplier_id,
            'currency' => strtoupper($request->currency),
            'exchange_rate' => $request->exchange_rate ?? 1,
            'import_type' => $request->import_type,
            'notes' => $request->notes,
            'total_freight' => $request->total_freight ?? 0,
            'total_tax' => $request->total_tax ?? 0,
            'status' => 'draft',
        ]);

        return redirect()->route('procurement.folders.show', $folder)
            ->with('status', 'Folder updated. Re-run cost analysis before submitting.');
    }

    public function destroy(ProcurementFolder $folder): RedirectResponse
    {
        if ($folder->status !== 'draft') {
            return back()->with('error', 'Only draft folders can be deleted.');
        }

        $folder->delete();

        return redirect()->route('procurement.folders.index')
            ->with('status', 'Procurement folder deleted.');
    }

    public function storeItem(ProcurementItemRequest $request, ProcurementFolder $folder): RedirectResponse
    {
        if (! $folder->canEdit()) {
            return back()->with('error', 'Cannot add items to this folder.');
        }

        $folder->items()->create($request->validated());

        return back()->with('status', 'Line item added.');
    }

    public function destroyItem(ProcurementFolder $folder, ProcurementItem $item): RedirectResponse
    {
        if ($item->procurement_folder_id !== $folder->id || ! $folder->canEdit()) {
            return back()->with('error', 'Cannot remove this item.');
        }

        $item->delete();

        return back()->with('status', 'Line item removed.');
    }

    public function runCostAnalysis(RunCostAnalysisRequest $request, ProcurementFolder $folder): RedirectResponse
    {
        try {
            $this->costAnalysis->analyze(
                $folder,
                $request->total_freight,
                $request->total_tax,
                (float) ($request->default_margin ?? 30)
            );

            return back()->with('status', 'Cost analysis completed.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submit(ProcurementFolder $folder): RedirectResponse
    {
        if (! $folder->canSubmit()) {
            return back()->with('error', 'Folder cannot be submitted for approval.');
        }

        try {
            $folder->requestApproval($folder->notes);
            $folder->update(['status' => 'pending_approval']);

            return back()->with('status', 'Folder submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function generatePo(ProcurementFolder $folder): RedirectResponse
    {
        try {
            $po = $this->procurement->generatePurchaseOrder($folder);

            return redirect()->route('purchase-orders.show', $po)
                ->with('status', 'Purchase order '.$po->po_number.' generated.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markInTransit(ProcurementFolder $folder): RedirectResponse
    {
        try {
            $this->procurement->markInTransit($folder);

            return back()->with('status', 'Folder and PO marked in transit.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function close(ProcurementFolder $folder): RedirectResponse
    {
        try {
            $this->procurement->closeFolder($folder);

            return back()->with('status', 'Procurement folder closed.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
