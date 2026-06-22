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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transfers,
        private InventoryService $inventory
    ) {
        $this->middleware('permission:transfers.view')->only(['index', 'show', 'availability']);
        $this->middleware('permission:transfers.request')->only(['create', 'store', 'submit', 'destroy']);
        $this->middleware('permission:transfers.dispatch')->only(['dispatch']);
        $this->middleware('permission:transfers.receive')->only(['receiveForm', 'receive']);
    }

    public function index(Request $request): View
    {
        $requests = TransferRequest::query()
            ->with(['source', 'destination', 'requester', 'stockTransfer'])
            ->withCount('items')
            ->withSum('items as total_units', 'requested_quantity')
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($inner) use ($term) {
                    $inner->where('request_number', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%")
                        ->orWhereHas('stockTransfer', fn ($st) => $st->where('transfer_number', 'like', "%{$term}%"));
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => TransferRequest::count(),
            'draft' => TransferRequest::where('status', 'draft')->count(),
            'pending' => TransferRequest::where('status', 'pending')->count(),
            'approved' => TransferRequest::where('status', 'approved')->count(),
            'in_transit' => TransferRequest::where('status', 'dispatched')->count(),
            'completed' => TransferRequest::where('status', 'completed')->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'count' => $stats['draft']],
            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'count' => $stats['pending']],
            ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'count' => $stats['approved']],
            ['key' => 'dispatched', 'label' => 'In Transit', 'icon' => 'fa-truck', 'count' => $stats['in_transit']],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => 'fa-flag-checkered', 'count' => $stats['completed']],
        ];

        return view('transfers.index', compact('requests', 'stats', 'pipeline'));
    }

    public function create(Request $request): View
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $shops = Shop::active()->orderBy('name')->get();
        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'unit_id']);

        $prefill = [
            'type' => $request->get('type', 'warehouse_to_shop'),
            'source_id' => $request->integer('source_id') ?: null,
            'destination_id' => $request->integer('destination_id') ?: null,
            'product_id' => $request->integer('product_id') ?: null,
        ];

        if ($prefill['type'] === 'warehouse_to_shop' && ! $prefill['source_id']) {
            $prefill['source_id'] = $warehouses->first()?->id;
        }
        if ($prefill['type'] === 'inter_shop' && ! $prefill['source_id']) {
            $prefill['source_id'] = $shops->first()?->id;
        }
        if (! $prefill['destination_id']) {
            $prefill['destination_id'] = $shops->first()?->id;
        }

        return view('transfers.create', compact('warehouses', 'shops', 'products', 'prefill'));
    }

    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:warehouse_to_shop,inter_shop'],
            'source_id' => ['required', 'integer', 'min:1'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $source = $this->resolveSource($request->type, (int) $request->source_id);
        $productIds = array_filter(array_map('intval', $request->input('product_ids', [])));

        if ($productIds === []) {
            return response()->json(['availability' => []]);
        }

        $availability = [];
        foreach (Product::whereIn('id', $productIds)->get(['id', 'part_number']) as $product) {
            $availability[$product->id] = [
                'available' => $this->inventory->available($product, $source),
                'part_number' => $product->part_number,
            ];
        }

        return response()->json(['availability' => $availability]);
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

        return redirect()->route('transfers.show', $transfer)
            ->with('status', 'Transfer saved as draft. Submit for approval when ready.');
    }

    public function show(TransferRequest $transfer): View
    {
        $transfer->load([
            'source', 'destination', 'requester', 'approver',
            'items.product.unit', 'stockTransfer.items.product.unit', 'stockTransfer.dispatcher', 'stockTransfer.receiver',
            'approval',
        ]);

        $sourceAvailability = [];
        if ($transfer->source && in_array($transfer->status, ['draft', 'returned', 'pending', 'approved'], true)) {
            foreach ($transfer->items as $item) {
                $sourceAvailability[$item->product_id] = $this->inventory->available($item->product, $transfer->source);
            }
        }

        return view('transfers.show', [
            'transfer' => $transfer,
            'sourceAvailability' => $sourceAvailability,
        ]);
    }

    public function submit(TransferRequest $transfer): RedirectResponse
    {
        if (! $transfer->canSubmit()) {
            return back()->with('error', 'This transfer cannot be submitted.');
        }

        try {
            $transfer->requestApproval($transfer->notes);
            $transfer->update(['status' => 'pending']);

            return back()->with('status', 'Transfer submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function dispatch(DispatchTransferRequest $request, TransferRequest $transfer): RedirectResponse
    {
        try {
            $this->transfers->dispatch($transfer, $request->notes);

            return redirect()->route('transfers.show', $transfer)
                ->with('status', 'Stock dispatched — transfer is now in transit.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receiveForm(TransferRequest $transfer): View|RedirectResponse
    {
        $transfer->load(['stockTransfer.items.product.unit', 'source', 'destination']);

        if (! $transfer->stockTransfer || ! $transfer->stockTransfer->canReceive()) {
            return redirect()->route('transfers.show', $transfer)
                ->with('error', 'This transfer cannot be received yet.');
        }

        return view('transfers.receive', ['transfer' => $transfer]);
    }

    public function receive(ReceiveTransferRequest $request, TransferRequest $transfer): RedirectResponse
    {
        if (! $transfer->stockTransfer) {
            return back()->with('error', 'No dispatch record found for this transfer.');
        }

        try {
            $this->transfers->receive(
                $transfer->stockTransfer,
                $request->items,
                $request->notes
            );

            return redirect()->route('transfers.show', $transfer)
                ->with('status', 'Receipt posted to inventory.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(TransferRequest $transfer): RedirectResponse
    {
        if ($transfer->status !== 'draft') {
            return back()->with('error', 'Only draft transfers can be deleted.');
        }

        $transfer->delete();

        return redirect()->route('transfers.index')
            ->with('status', 'Draft transfer deleted.');
    }

    private function resolveSource(string $type, int $sourceId): Warehouse|Shop
    {
        if ($type === 'warehouse_to_shop') {
            return Warehouse::findOrFail($sourceId);
        }

        return Shop::findOrFail($sourceId);
    }
}
