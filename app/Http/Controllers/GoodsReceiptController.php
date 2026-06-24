<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Http\Requests\ClosePurchaseOrderShortRequest;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Http\Requests\VoidGoodsReceiptRequest;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteItem;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\WarehouseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(
        private GoodsReceiptService $goodsReceipt,
        private WarehouseAccessService $warehouseAccess,
    ) {
        $this->middleware('permission:procurement.view')->only(['index', 'show']);
        $this->middleware('permission:procurement.manage')->only(['create', 'store', 'void']);
    }

    public function index(Request $request): View
    {
        $postedScope = GoodsReceiptNote::query()->posted();

        $stats = [
            'total' => (clone $postedScope)->count(),
            'this_month' => (clone $postedScope)->whereMonth('received_at', now()->month)
                ->whereYear('received_at', now()->year)
                ->count(),
            'total_received' => GoodsReceiptNoteItem::normalizeQuantity(
                GoodsReceiptNoteItem::query()
                    ->whereHas('goodsReceiptNote', fn ($q) => $q->posted())
                    ->sum('received_quantity')
            ),
            'total_damaged' => GoodsReceiptNoteItem::normalizeQuantity(
                GoodsReceiptNoteItem::query()
                    ->whereHas('goodsReceiptNote', fn ($q) => $q->posted())
                    ->sum('damaged_quantity')
            ),
            'with_damage' => (clone $postedScope)->whereHas('items', fn ($q) => $q->where('damaged_quantity', '>', 0))->count(),
        ];
        $stats['total_good'] = max(0, round($stats['total_received'] - $stats['total_damaged'], 2));

        $scopedWarehouseId = $this->warehouseAccess->scopedWarehouseId(auth()->user());

        $receipts = GoodsReceiptNote::query()
            ->with(['warehouse', 'receiver', 'purchaseOrder.supplier', 'quotationSeries'])
            ->withCount('items')
            ->withSum('items as total_received_qty', 'received_quantity')
            ->withSum('items as total_damaged_qty', 'damaged_quantity')
            ->when($scopedWarehouseId, fn ($q) => $q->where('warehouse_id', $scopedWarehouseId))
            ->when($request->search, function ($query) use ($request) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('grn_number', 'like', "%{$term}%")
                        ->orWhereHas('purchaseOrder', fn ($p) => $p->where('po_number', 'like', "%{$term}%"))
                        ->orWhereHas('purchaseOrder.supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->warehouse_id, fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->status === 'voided', fn ($q) => $q->where('status', 'voided'))
            ->when($request->status === 'posted', fn ($q) => $q->posted())
            ->when(! $request->status, fn ($q) => $q->posted())
            ->when($request->damage === 'yes', fn ($q) => $q->whereHas('items', fn ($i) => $i->where('damaged_quantity', '>', 0)))
            ->when($request->damage === 'no', fn ($q) => $q->whereDoesntHave('items', fn ($i) => $i->where('damaged_quantity', '>', 0)))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest('received_at'))
            ->when($request->sort === 'items', fn ($q) => $q->orderByDesc('items_count'))
            ->when(! in_array($request->sort, ['oldest', 'items'], true), fn ($q) => $q->latest('received_at'))
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::query()
            ->whereIn('id', GoodsReceiptNote::query()->select('warehouse_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('goods-receipts.index', compact('receipts', 'stats', 'warehouses'));
    }

    public function create(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (! $purchaseOrder->canReceive()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot receive goods.');
        }

        $purchaseOrder->load(['items.product.unit', 'supplier', 'quotationSeries']);

        $warehouses = Warehouse::active()->orderBy('name')->get();
        $scopedWarehouseId = $this->warehouseAccess->scopedWarehouseId(auth()->user());
        $defaultWarehouseId = $scopedWarehouseId ?: $warehouses->first()?->id;

        return view('goods-receipts.create', compact('purchaseOrder', 'warehouses', 'defaultWarehouseId', 'scopedWarehouseId'));
    }

    public function store(StoreGoodsReceiptRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $grn = $this->goodsReceipt->receive(
                $purchaseOrder,
                (int) $request->warehouse_id,
                $request->items,
                $request->notes
            );

            return redirect()->route('goods-receipts.show', $grn)
                ->with('status', 'Goods receipt '.$grn->grn_number.' posted to inventory.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(GoodsReceiptNote $goodsReceiptNote): View
    {
        $goodsReceiptNote->load([
            'items.product.unit', 'items.product.productName',
            'warehouse', 'receiver', 'voidedBy',
            'purchaseOrder.supplier', 'purchaseOrder.quotationSeries',
            'quotationSeries',
        ]);

        return view('goods-receipts.show', compact('goodsReceiptNote'));
    }

    public function void(VoidGoodsReceiptRequest $request, GoodsReceiptNote $goodsReceiptNote): RedirectResponse
    {
        try {
            $this->goodsReceipt->void($goodsReceiptNote, $request->reason);

            return redirect()->route('goods-receipts.show', $goodsReceiptNote)
                ->with('status', 'Goods receipt '.$goodsReceiptNote->grn_number.' has been voided and inventory reversed.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
