<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Exceptions\InventoryException;
use App\Http\Requests\DispatchTransferRequest;
use App\Http\Requests\ReceiveStockTransferRequest;
use App\Http\Requests\StoreStockTransferRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\StockTransferAccessService;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(
        private StockTransferAccessService $access,
        private TransferService $transfers,
        private InventoryService $inventory,
    ) {
        $this->middleware('permission:transfers.view')->only(['index', 'show', 'availability']);
        $this->middleware('permission:transfers.create')->only(['create', 'store', 'submit', 'destroy']);
        $this->middleware('permission:transfers.dispatch')->only(['dispatch']);
        $this->middleware('permission:transfers.receive')->only(['receiveForm', 'receive']);
    }

    public function index(Request $request): View
    {
        $baseQuery = StockTransfer::query();
        $this->access->scopeVisible($baseQuery, auth()->user());

        $transfers = (clone $baseQuery)
            ->with(['source', 'destination', 'creator', 'transferRequest'])
            ->withCount('items')
            ->withSum('items as total_units', 'quantity')
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($inner) use ($term) {
                    $inner->where('transfer_number', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%")
                        ->orWhereHas('transferRequest', fn ($tr) => $tr->where('request_number', 'like', "%{$term}%"));
                });
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'in_transit') {
                    $q->whereIn('status', ['dispatched', 'in_transit']);
                } elseif ($request->status === 'closed') {
                    $q->whereIn('status', ['received', 'closed']);
                } else {
                    $q->where('status', $request->status);
                }
            })
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'in_transit' => (clone $baseQuery)->whereIn('status', ['dispatched', 'in_transit'])->count(),
            'closed' => (clone $baseQuery)->whereIn('status', ['received', 'closed'])->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'count' => $stats['draft']],
            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'count' => $stats['pending']],
            ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'count' => $stats['approved']],
            ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-truck', 'count' => $stats['in_transit']],
            ['key' => 'closed', 'label' => 'Completed', 'icon' => 'fa-flag-checkered', 'count' => $stats['closed']],
        ];

        $allowedCreateTypes = $this->access->allowedCreateTypes(auth()->user());

        return view('stock-transfers.index', compact('transfers', 'stats', 'pipeline', 'allowedCreateTypes'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $allowedTypes = $this->access->allowedCreateTypes($user);

        if ($allowedTypes === []) {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'You are not allowed to create stock transfers.');
        }

        $warehouses = Warehouse::active()->orderBy('name')->get();
        $shops = Shop::active()->orderBy('name')->get();
        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'unit_id']);

        $scopedWarehouseId = $this->access->scopedWarehouseId($user);
        $defaultType = in_array($request->get('type'), $allowedTypes, true)
            ? $request->get('type')
            : ($scopedWarehouseId ? 'warehouse_to_shop' : 'warehouse_to_shop');

        $prefill = [
            'type' => $defaultType,
            'source_id' => $request->integer('source_id') ?: null,
            'destination_id' => $request->integer('destination_id') ?: null,
            'product_id' => $request->integer('product_id') ?: null,
        ];

        if ($prefill['type'] === 'warehouse_to_shop') {
            $prefill['source_id'] = $scopedWarehouseId ?: $prefill['source_id'] ?: $warehouses->first()?->id;
            $prefill['destination_id'] = $prefill['destination_id'] ?: $shops->first()?->id;
        }

        if ($prefill['type'] === 'shop_to_warehouse') {
            $prefill['source_id'] = $prefill['source_id'] ?: $shops->first()?->id;
            $prefill['destination_id'] = $scopedWarehouseId ?: $prefill['destination_id'] ?: $warehouses->first()?->id;
        }

        if ($prefill['type'] === 'inter_shop') {
            $prefill['source_id'] = $prefill['source_id'] ?: $shops->first()?->id;
            $prefill['destination_id'] = $prefill['destination_id'] ?: $shops->skip(1)->first()?->id;
        }

        $lockedWarehouse = $scopedWarehouseId ? $warehouses->firstWhere('id', $scopedWarehouseId) : null;

        return view('stock-transfers.create', compact(
            'warehouses',
            'shops',
            'products',
            'prefill',
            'allowedTypes',
            'lockedWarehouse',
        ));
    }

    public function availability(Request $request): JsonResponse
    {
        $user = auth()->user();
        $allowedTypes = $this->access->allowedCreateTypes($user);

        $request->validate([
            'type' => ['required', Rule::in($allowedTypes)],
            'source_id' => ['required', 'integer', 'min:1'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $scopedWarehouseId = $this->access->scopedWarehouseId($user);
        if ($scopedWarehouseId && in_array($request->type, ['warehouse_to_shop', 'shop_to_warehouse'], true)) {
            $expectedSource = $request->type === 'warehouse_to_shop';
            $sourceType = $expectedSource ? 'source' : 'destination';
            if ((int) $request->source_id !== $scopedWarehouseId && $request->type === 'warehouse_to_shop') {
                abort(403, 'You can only check availability for your assigned warehouse.');
            }
        }

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

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $source = $request->sourceModel();
        $destination = $request->destinationModel();

        $transfer = StockTransfer::create([
            'transfer_number' => StockTransfer::generateNumber(),
            'type' => $request->type,
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
            'destination_type' => $destination->getMorphClass(),
            'destination_id' => $destination->getKey(),
            'status' => 'draft',
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $transfer->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['requested_quantity'],
            ]);
        }

        return redirect()->route('stock-transfers.show', $transfer)
            ->with('status', 'Stock transfer saved as draft. Submit for approval when ready.');
    }

    public function show(StockTransfer $stockTransfer): View|RedirectResponse
    {
        if (! $this->access->canView(auth()->user(), $stockTransfer)) {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'You cannot view this stock transfer.');
        }

        $stockTransfer->load([
            'source', 'destination', 'creator', 'approver',
            'items.product.unit', 'transferRequest', 'dispatcher', 'receiver',
            'approval',
        ]);

        $sourceAvailability = [];
        if ($stockTransfer->source && in_array($stockTransfer->status, ['draft', 'returned', 'pending', 'approved'], true)) {
            foreach ($stockTransfer->items as $item) {
                $sourceAvailability[$item->product_id] = $this->inventory->available($item->product, $stockTransfer->source);
            }
        }

        $user = auth()->user();
        $isReadOnlyInbound = $user->hasRole('Shop Manager')
            && $stockTransfer->destination_type === Shop::class
            && (int) $stockTransfer->destination_id === $this->access->scopedShopId($user)
            && ! $this->access->canManage($user, $stockTransfer);

        return view('stock-transfers.show', [
            'stockTransfer' => $stockTransfer,
            'sourceAvailability' => $sourceAvailability,
            'canManage' => $this->access->canManage($user, $stockTransfer),
            'canReceive' => $this->access->canReceive($user, $stockTransfer),
            'canDispatch' => $this->access->canDispatch($user, $stockTransfer),
            'isReadOnlyInbound' => $isReadOnlyInbound,
        ]);
    }

    public function submit(StockTransfer $stockTransfer): RedirectResponse
    {
        if (! $this->access->canManage(auth()->user(), $stockTransfer)) {
            return back()->with('error', 'You are not allowed to submit this transfer.');
        }

        if (! $stockTransfer->canSubmit()) {
            return back()->with('error', 'This transfer cannot be submitted.');
        }

        try {
            $stockTransfer->requestApproval($stockTransfer->notes);
            $stockTransfer->update(['status' => 'pending']);

            return back()->with('status', 'Stock transfer submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function dispatch(DispatchTransferRequest $request, StockTransfer $stockTransfer): RedirectResponse
    {
        if (! $this->access->canDispatch(auth()->user(), $stockTransfer)) {
            return back()->with('error', 'You are not allowed to dispatch this transfer.');
        }

        try {
            $this->transfers->dispatch($stockTransfer, $request->notes);

            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('status', 'Stock marked in transit — destination can now receive.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receiveForm(StockTransfer $stockTransfer): View|RedirectResponse
    {
        if (! $this->access->canView(auth()->user(), $stockTransfer)) {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'You cannot view this transfer.');
        }

        if (! $this->access->canReceive(auth()->user(), $stockTransfer)) {
            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('error', 'You are not allowed to receive this transfer.');
        }

        $stockTransfer->load(['items.product.unit', 'source', 'destination', 'dispatcher']);

        if (! $stockTransfer->canReceive()) {
            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('error', 'This transfer cannot be received yet.');
        }

        return view('stock-transfers.receive', ['stockTransfer' => $stockTransfer]);
    }

    public function receive(ReceiveStockTransferRequest $request, StockTransfer $stockTransfer): RedirectResponse
    {
        try {
            $this->transfers->receive(
                $stockTransfer,
                $request->items,
                $request->notes
            );

            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('status', 'Receipt posted to inventory.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(StockTransfer $stockTransfer): RedirectResponse
    {
        if (! $this->access->canManage(auth()->user(), $stockTransfer)) {
            return back()->with('error', 'You are not allowed to delete this transfer.');
        }

        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Only draft transfers can be deleted.');
        }

        $stockTransfer->delete();

        return redirect()->route('stock-transfers.index')
            ->with('status', 'Draft stock transfer deleted.');
    }

    private function resolveSource(string $type, int $sourceId): Warehouse|Shop
    {
        return $type === 'warehouse_to_shop'
            ? Warehouse::findOrFail($sourceId)
            : Shop::findOrFail($sourceId);
    }
}
