<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:procurement.view');
    }

    public function index(Request $request): View
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier', 'folder', 'creator'])
            ->when($request->search, fn ($q) => $q->where('po_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase-orders.index', compact('orders'));
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier', 'folder', 'creator', 'items.product.unit',
            'goodsReceiptNotes.items', 'goodsReceiptNotes.warehouse',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }
}
