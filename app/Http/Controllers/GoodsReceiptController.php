<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $goodsReceipt)
    {
        $this->middleware('permission:procurement.view')->only(['show']);
        $this->middleware('permission:procurement.manage')->only(['create', 'store']);
    }

    public function create(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (! $purchaseOrder->canReceive()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot receive goods.');
        }

        $purchaseOrder->load(['items.product.unit', 'supplier']);
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
            'items.product.unit', 'warehouse', 'receiver',
            'purchaseOrder.supplier', 'folder',
        ]);

        return view('goods-receipts.show', compact('goodsReceiptNote'));
    }
}
