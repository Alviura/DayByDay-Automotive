<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Exceptions\InventoryException;
use App\Http\Requests\DispatchTransferRequest;
use App\Http\Requests\ReceiveTransferRequest;
use App\Http\Requests\StoreTransferRequestRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferRequestController extends Controller
{
    public function __construct(
        private TransferService $transfers,
        private InventoryService $inventory
    ) {
        $this->middleware('permission:transfers.view')->only(['index', 'show']);
        $this->middleware('permission:transfers.request')->only(['create', 'store', 'submit', 'destroy']);
        $this->middleware('permission:transfers.dispatch')->only(['dispatch']);
    }

    public function index(Request $request): View
    {
        $requests = TransferRequest::query()
            ->with(['source', 'destination', 'requester', 'items'])
            ->withCount('items')
            ->when($request->search, fn ($q) => $q->where('request_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => TransferRequest::count(),
            'draft' => TransferRequest::where('status', 'draft')->count(),
            'pending' => TransferRequest::where('status', 'pending')->count(),
            'in_transit' => TransferRequest::where('status', 'dispatched')->count(),
        ];

        return view('transfers.requests.index', compact('requests', 'stats'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $shops = Shop::active()->orderBy('name')->get();
        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'unit_id']);

        return view('transfers.requests.create', compact('warehouses', 'shops', 'products'));
    }

    public function store(StoreTransferRequestRequest $request): RedirectResponse
    {
        $source = $request->sourceModel();
        $destination = $request->destinationModel();

        $transfer = TransferRequest::create([
            'request_number' => TransferRequest::generateNumber(),
            'type' => $request->type,
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
            'destination_type' => $destination->getMorphClass(),
            'destination_id' => $destination->getKey(),
            'status' => 'draft',
            'notes' => $request->notes,
            'requested_by' => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            $transfer->items()->create([
                'product_id' => $item['product_id'],
                'requested_quantity' => $item['requested_quantity'],
            ]);
        }

        return redirect()->route('transfer-requests.show', $transfer)
            ->with('status', 'Transfer request created. Submit for approval when ready.');
    }

    public function show(TransferRequest $transferRequest): View
    {
        $transferRequest->load([
            'source', 'destination', 'requester', 'approver',
            'items.product.unit', 'stockTransfer.items.product', 'approval',
        ]);

        return view('transfers.requests.show', ['request' => $transferRequest]);
    }

    public function submit(TransferRequest $transferRequest): RedirectResponse
    {
        if (! $transferRequest->canSubmit()) {
            return back()->with('error', 'This request cannot be submitted.');
        }

        try {
            $transferRequest->requestApproval($transferRequest->notes);
            $transferRequest->update(['status' => 'pending']);

            return back()->with('status', 'Transfer request submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function dispatch(DispatchTransferRequest $request, TransferRequest $transferRequest): RedirectResponse
    {
        try {
            $transfer = $this->transfers->dispatch($transferRequest, $request->notes);

            return redirect()->route('stock-transfers.show', $transfer)
                ->with('status', 'Stock dispatched as '.$transfer->transfer_number.'.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(TransferRequest $transferRequest): RedirectResponse
    {
        if ($transferRequest->status !== 'draft') {
            return back()->with('error', 'Only draft requests can be deleted.');
        }

        $transferRequest->delete();

        return redirect()->route('transfer-requests.index')
            ->with('status', 'Transfer request deleted.');
    }
}
