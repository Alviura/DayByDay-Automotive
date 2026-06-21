<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteItem;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $goodsReceipt)
    {
        $this->middleware('permission:procurement.view')->only(['index', 'show']);
        $this->middleware('permission:procurement.manage')->only(['create', 'store']);
    }

    public function index(Request $request): View
    {
        $stats = [
            'total' => GoodsReceiptNote::count(),
            'this_month' => GoodsReceiptNote::whereMonth('received_at', now()->month)
                ->whereYear('received_at', now()->year)
                ->count(),
            'total_received' => GoodsReceiptNoteItem::normalizeQuantity(GoodsReceiptNoteItem::sum('received_quantity')),
            'total_damaged' => GoodsReceiptNoteItem::normalizeQuantity(GoodsReceiptNoteItem::sum('damaged_quantity')),
            'with_damage' => GoodsReceiptNote::whereHas('items', fn ($q) => $q->where('damaged_quantity', '>', 0))->count(),
        ];
        $stats['total_good'] = max(0, round($stats['total_received'] - $stats['total_damaged'], 2));

        $receipts = GoodsReceiptNote::query()
            ->with(['warehouse', 'receiver', 'purchaseOrder.supplier', 'quotationSeries'])
            ->withCount('items')
            ->withSum('items as total_received_qty', 'received_quantity')
            ->withSum('items as total_damaged_qty', 'damaged_quantity')
            ->when($request->search, function ($query) use ($request) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('grn_number', 'like', "%{$term}%")
                        ->orWhereHas('purchaseOrder', fn ($p) => $p->where('po_number', 'like', "%{$term}%"))
                        ->orWhereHas('purchaseOrder.supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->warehouse_id, fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
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

        return view('goods-receipts.create', compact('purchaseOrder', 'warehouses'));
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
            'warehouse', 'receiver',
            'purchaseOrder.supplier', 'purchaseOrder.quotationSeries',
            'quotationSeries',
        ]);

        return view('goods-receipts.show', compact('goodsReceiptNote'));
    }
}
