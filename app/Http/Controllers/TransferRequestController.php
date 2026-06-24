<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewTransferRequestRequest;
use App\Http\Requests\StoreTransferRequestRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\NotificationRecipientService;
use App\Services\TransferRequestAccessService;
use App\Services\TransferService;
use App\Notifications\TransferRequestSubmittedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransferRequestController extends Controller
{
    public function __construct(
        private TransferRequestAccessService $access,
        private TransferService $transfers,
        private InventoryService $inventory,
        private NotificationRecipientService $notificationRecipients,
    ) {
        $this->middleware('permission:transfer_requests.view')->only(['index', 'show', 'availability']);
        $this->middleware('permission:transfer_requests.create')->only(['create', 'store', 'submit', 'destroy']);
        $this->middleware('permission:transfer_requests.review')->only(['accept', 'reject', 'createStockTransfer']);
    }

    public function index(Request $request): View
    {
        $baseQuery = TransferRequest::query();
        $this->access->scopeVisible($baseQuery, auth()->user());

        $requests = (clone $baseQuery)
            ->with(['source', 'destination', 'requester', 'reviewer', 'stockTransfer'])
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
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'accepted' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'fulfilled' => (clone $baseQuery)->where('status', 'fulfilled')->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'count' => $stats['draft']],
            ['key' => 'submitted', 'label' => 'Awaiting Review', 'icon' => 'fa-hourglass-half', 'count' => $stats['submitted']],
            ['key' => 'accepted', 'label' => 'Accepted', 'icon' => 'fa-circle-check', 'count' => $stats['accepted']],
            ['key' => 'fulfilled', 'label' => 'Fulfilled', 'icon' => 'fa-flag-checkered', 'count' => $stats['fulfilled']],
        ];

        return view('transfer-requests.index', compact('requests', 'stats', 'pipeline'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->access->canCreate(auth()->user())) {
            return redirect()->route('transfer-requests.index')
                ->with('error', 'You are not allowed to create transfer requests.');
        }

        $user = auth()->user();
        $isAdmin = $this->access->isAdministrator($user);
        $scopedShopId = $this->access->scopedShopId($user);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $destinationShops = Shop::active()->orderBy('name')->get();
        $lockedShop = $isAdmin ? null : Shop::find($scopedShopId);
        $shops = $isAdmin
            ? $destinationShops
            : $destinationShops->where('id', '!=', $scopedShopId)->values();

        $products = Product::active()->with('unit')->orderBy('name')->get(['id', 'part_number', 'name', 'unit_id']);

        $defaultType = in_array($request->get('type'), ['warehouse_to_shop', 'inter_shop'], true)
            ? $request->get('type')
            : 'warehouse_to_shop';

        $prefill = [
            'type' => $defaultType,
            'source_id' => $request->integer('source_id') ?: null,
            'destination_id' => $request->integer('destination_id') ?: ($lockedShop?->id),
            'product_id' => $request->integer('product_id') ?: null,
        ];

        if ($prefill['type'] === 'warehouse_to_shop' && ! $prefill['source_id']) {
            $prefill['source_id'] = $warehouses->first()?->id;
        }

        if ($prefill['type'] === 'inter_shop' && ! $prefill['source_id']) {
            $prefill['source_id'] = $shops->firstWhere('id', '!=', $prefill['destination_id'])?->id
                ?? $shops->first()?->id;
        }

        if ($isAdmin && ! $prefill['destination_id']) {
            $prefill['destination_id'] = $destinationShops->first()?->id;
        }

        return view('transfer-requests.create', compact(
            'warehouses',
            'shops',
            'destinationShops',
            'products',
            'prefill',
            'lockedShop',
            'isAdmin',
        ));
    }

    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(['warehouse_to_shop', 'inter_shop'])],
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

        $transferRequest = TransferRequest::create([
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
            $transferRequest->items()->create([
                'product_id' => $item['product_id'],
                'requested_quantity' => $item['requested_quantity'],
            ]);
        }

        return redirect()->route('transfer-requests.show', $transferRequest)
            ->with('status', 'Transfer request saved as draft. Submit when ready for review.');
    }

    public function show(TransferRequest $transferRequest): View|RedirectResponse
    {
        if (! $this->access->canView(auth()->user(), $transferRequest)) {
            return redirect()->route('transfer-requests.index')
                ->with('error', 'You cannot view this transfer request.');
        }

        $transferRequest->load([
            'source', 'destination', 'requester', 'reviewer',
            'items.product.unit', 'stockTransfer',
        ]);

        $sourceAvailability = [];
        if ($transferRequest->source && in_array($transferRequest->status, ['draft', 'submitted'], true)) {
            foreach ($transferRequest->items as $item) {
                $sourceAvailability[$item->product_id] = $this->inventory->available($item->product, $transferRequest->source);
            }
        }

        $user = auth()->user();

        return view('transfer-requests.show', [
            'transferRequest' => $transferRequest,
            'sourceAvailability' => $sourceAvailability,
            'canManage' => $this->access->canManage($user, $transferRequest),
            'canReview' => $this->access->canReview($user, $transferRequest),
            'canCreateTransfer' => $this->access->canCreateTransferFrom($user, $transferRequest),
        ]);
    }

    public function submit(TransferRequest $transferRequest): RedirectResponse
    {
        if (! $this->access->canManage(auth()->user(), $transferRequest)) {
            return back()->with('error', 'You are not allowed to submit this request.');
        }

        if (! $transferRequest->canSubmit()) {
            return back()->with('error', 'This request cannot be submitted.');
        }

        $transferRequest->update(['status' => 'submitted']);

        $this->notificationRecipients->notifyMany(
            $this->notificationRecipients->reviewersForTransferRequest($transferRequest),
            new TransferRequestSubmittedNotification($transferRequest)
        );

        return back()->with('status', 'Transfer request submitted for review.');
    }

    public function accept(ReviewTransferRequestRequest $request, TransferRequest $transferRequest): RedirectResponse
    {
        $user = auth()->user();

        if (! $this->access->canReview($user, $transferRequest)) {
            return back()->with('error', 'You are not allowed to review this request.');
        }

        if (! $user->can('transfers.create') && ! $this->access->isAdministrator($user)) {
            return back()->with('error', 'You are not allowed to issue stock for this request.');
        }

        try {
            $stockTransfer = DB::transaction(function () use ($transferRequest, $request, $user) {
                $transferRequest->update([
                    'status' => 'accepted',
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'review_notes' => $request->notes,
                ]);

                return $this->transfers->createFromTransferRequest($transferRequest->fresh(), $user);
            });

            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('status', 'Request accepted — stock transfer created. Submit it for administrator approval before dispatch.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not create stock transfer: '.$e->getMessage());
        }
    }

    public function reject(ReviewTransferRequestRequest $request, TransferRequest $transferRequest): RedirectResponse
    {
        if (! $this->access->canReview(auth()->user(), $transferRequest)) {
            return back()->with('error', 'You are not allowed to review this request.');
        }

        $transferRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->notes,
        ]);

        return back()->with('status', 'Transfer request rejected.');
    }

    public function createStockTransfer(TransferRequest $transferRequest): RedirectResponse
    {
        $user = auth()->user();

        if (! $this->access->canCreateTransferFrom($user, $transferRequest)) {
            return back()->with('error', 'You cannot create a stock transfer for this request.');
        }

        $stockTransfer = $this->transfers->createFromTransferRequest($transferRequest, $user);

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('status', 'Stock transfer created from request. Submit for administrator approval.');
    }

    public function destroy(TransferRequest $transferRequest): RedirectResponse
    {
        if (! $this->access->canManage(auth()->user(), $transferRequest)) {
            return back()->with('error', 'You are not allowed to delete this request.');
        }

        if ($transferRequest->status !== 'draft') {
            return back()->with('error', 'Only draft requests can be deleted.');
        }

        $transferRequest->delete();

        return redirect()->route('transfer-requests.index')
            ->with('status', 'Draft transfer request deleted.');
    }

    private function resolveSource(string $type, int $sourceId): Warehouse|Shop
    {
        return $type === 'warehouse_to_shop'
            ? Warehouse::findOrFail($sourceId)
            : Shop::findOrFail($sourceId);
    }
}
