<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Http\Requests\StoreCustomerReturnRequest;
use App\Http\Requests\StoreSupplierReturnRequest;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:returns.view')->only(['index', 'show', 'saleItems']);
        $this->middleware('permission:returns.create')->only(['create', 'store', 'submit', 'destroy']);
    }

    public function index(Request $request): View
    {
        $returns = $this->baseQuery('customer', $request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = $this->stats('customer');

        return view('returns.customer.index', compact('returns', 'stats'));
    }

    public function create(): View
    {
        $sales = Sale::query()
            ->where('status', 'completed')
            ->with(['shop', 'items'])
            ->when($shopId = $this->scopedShopId(), fn ($q) => $q->where('shop_id', $shopId))
            ->latest('sold_at')
            ->limit(50)
            ->get(['id', 'receipt_number', 'shop_id', 'customer_name', 'total', 'sold_at']);

        return view('returns.customer.create', compact('sales'));
    }

    public function store(StoreCustomerReturnRequest $request): RedirectResponse
    {
        $sale = $request->sale();

        if ($redirect = $this->authorizeShopAccess($sale->shop_id)) {
            return $redirect;
        }

        $return = ReturnRecord::create([
            'return_number' => ReturnRecord::generateNumber('customer'),
            'type' => 'customer',
            'sale_id' => $sale->id,
            'shop_id' => $sale->shop_id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        foreach ($request->items as $line) {
            $saleItem = $sale->items->firstWhere('product_id', $line['product_id']);

            $return->items()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'] ?? $saleItem?->unit_price,
                'condition' => $line['condition'],
                'restock' => (bool) ($line['restock'] ?? ($line['condition'] === 'good')),
                'replacement' => (bool) ($line['replacement'] ?? false),
            ]);
        }

        return redirect()->route('customer-returns.show', $return)
            ->with('status', 'Customer return created. Submit for approval when ready.');
    }

    public function show(ReturnRecord $customerReturn): View|RedirectResponse
    {
        if ($customerReturn->type !== 'customer') {
            abort(404);
        }

        if ($redirect = $this->authorizeShopAccess($customerReturn->shop_id)) {
            return $redirect;
        }

        $customerReturn->load([
            'sale.items.product', 'shop', 'items.product.unit',
            'approver', 'processor', 'approval',
        ]);

        return view('returns.customer.show', ['return' => $customerReturn]);
    }

    public function submit(ReturnRecord $customerReturn): RedirectResponse
    {
        if ($customerReturn->type !== 'customer') {
            abort(404);
        }

        if (! $customerReturn->canSubmit()) {
            return back()->with('error', 'This return cannot be submitted.');
        }

        try {
            $customerReturn->requestApproval($customerReturn->reason);

            return back()->with('status', 'Return submitted for approval.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(ReturnRecord $customerReturn): RedirectResponse
    {
        if ($customerReturn->type !== 'customer' || ! $customerReturn->canDelete()) {
            return back()->with('error', 'This return cannot be deleted.');
        }

        $customerReturn->delete();

        return redirect()->route('customer-returns.index')
            ->with('status', 'Return deleted.');
    }

    public function saleItems(Sale $sale): JsonResponse
    {
        if ($sale->status !== 'completed') {
            return response()->json(['error' => 'Invalid sale'], 422);
        }

        $scoped = $this->scopedShopId();
        if ($scoped && (int) $sale->shop_id !== $scoped) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $sale->load(['items.product.unit']);

        return response()->json(
            $sale->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'part_number' => $item->product->part_number,
                'name' => $item->product->name,
                'sold_quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'unit' => $item->product->unit?->abbreviation,
            ])
        );
    }

    private function baseQuery(string $type, Request $request)
    {
        $query = ReturnRecord::query()
            ->where('type', $type)
            ->with(['shop', 'supplier', 'warehouse', 'sale', 'items']);

        if ($shopId = $this->scopedShopId()) {
            $query->where('shop_id', $shopId);
        }

        return $query
            ->when($request->search, fn ($q) => $q->where('return_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status));
    }

    private function stats(string $type): array
    {
        $query = ReturnRecord::where('type', $type);

        if ($shopId = $this->scopedShopId()) {
            $query->where('shop_id', $shopId);
        }

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];
    }

    private function scopedShopId(): ?int
    {
        $user = auth()->user();

        if ($user->hasRole('Shop Manager') && $user->shop_id) {
            return (int) $user->shop_id;
        }

        return null;
    }

    private function authorizeShopAccess(?int $shopId): ?RedirectResponse
    {
        $scoped = $this->scopedShopId();

        if ($scoped && $shopId && (int) $shopId !== $scoped) {
            return redirect()->route('customer-returns.index')
                ->with('error', 'You cannot access returns for this shop.');
        }

        return null;
    }
}
