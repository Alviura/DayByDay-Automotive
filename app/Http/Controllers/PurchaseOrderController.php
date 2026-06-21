<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
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
        $stats = [
            'total' => PurchaseOrder::count(),
            'this_month' => PurchaseOrder::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'in_transit' => PurchaseOrder::where('delivery_status', 'in_transit')->count(),
            'pending' => PurchaseOrder::where('delivery_status', 'pending')
                ->whereIn('status', ['sent', 'partially_received'])
                ->count(),
            'partial' => PurchaseOrder::where('status', 'partially_received')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'total_value' => (float) PurchaseOrder::sum('total'),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-layer-group', 'count' => $stats['total'], 'param' => 'delivery'],
            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-clock', 'count' => $stats['pending'], 'param' => 'delivery'],
            ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-truck', 'count' => $stats['in_transit'], 'param' => 'delivery'],
            ['key' => 'partially_received', 'label' => 'Partial', 'icon' => 'fa-boxes-stacked', 'count' => $stats['partial'], 'param' => 'status'],
            ['key' => 'received', 'label' => 'Complete', 'icon' => 'fa-circle-check', 'count' => $stats['received'], 'param' => 'status'],
        ];

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'quotationSeries', 'creator', 'items'])
            ->withCount('items')
            ->when($request->search, function ($query) use ($request) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('po_number', 'like', "%{$term}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('quotationSeries', fn ($qs) => $qs->where('title', 'like', "%{$term}%")
                            ->orWhere('series_number', 'like', "%{$term}%"));
                });
            })
            ->when($request->delivery, fn ($q) => $q->where('delivery_status', $request->delivery))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort === 'total', fn ($q) => $q->orderByDesc('total'))
            ->when($request->sort === 'supplier', fn ($q) => $q->orderBy(
                Supplier::select('name')->whereColumn('suppliers.id', 'purchase_orders.supplier_id')
            ))
            ->when(! in_array($request->sort, ['oldest', 'total', 'supplier'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::query()
            ->whereIn('id', PurchaseOrder::query()->select('supplier_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('purchase-orders.index', compact('orders', 'stats', 'pipeline', 'suppliers'));
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier', 'quotationSeries.supplier', 'creator', 'items.product.unit', 'items.product.productName',
            'goodsReceiptNotes.items', 'goodsReceiptNotes.warehouse',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }
}
