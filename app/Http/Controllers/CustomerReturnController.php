<?php

namespace App\Http\Controllers;

use App\Exceptions\ApprovalException;
use App\Http\Requests\StoreCustomerReturnRequest;
use App\Models\ReturnRecord;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:returns.view')->only(['index', 'show', 'saleItems', 'searchSales']);
        $this->middleware('permission:returns.create')->only(['create', 'store', 'submit', 'destroy']);
    }

    public function index(Request $request): View
    {
        $returns = $this->baseQuery($request)
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = $this->stats();
        $pipeline = $this->pipeline($stats);

        return view('returns.customer.index', compact('returns', 'stats', 'pipeline'));
    }

    public function create(Request $request): View
    {
        $prefillSaleId = $request->integer('sale_id') ?: null;

        return view('returns.customer.create', compact('prefillSaleId'));
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
            'status' => 'draft',
        ]);

        foreach ($request->items as $line) {
            $saleItem = $sale->items->firstWhere('product_id', $line['product_id']);

            $return->items()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'] ?? $saleItem?->unit_price,
                'condition' => $line['condition'],
                'restock' => (bool) ($line['restock'] ?? ($line['condition'] === 'good')),
                'replacement' => false,
            ]);
        }

        return redirect()->route('customer-returns.show', $return)
            ->with('status', 'Customer return draft created. Review and submit for approval.');
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
            'sale.items.product', 'sale.customerAccount', 'shop', 'items.product.unit',
            'approver', 'processor', 'approval.actions.actor',
        ]);

        return view('returns.customer.show', ['return' => $customerReturn]);
    }

    public function submit(ReturnRecord $customerReturn): RedirectResponse
    {
        if ($customerReturn->type !== 'customer') {
            abort(404);
        }

        if ($redirect = $this->authorizeShopAccess($customerReturn->shop_id)) {
            return $redirect;
        }

        if (! $customerReturn->canSubmit()) {
            return back()->with('error', 'This return cannot be submitted.');
        }

        try {
            $customerReturn->requestApproval($customerReturn->reason);
            $customerReturn->update(['status' => 'pending']);

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

        if ($redirect = $this->authorizeShopAccess($customerReturn->shop_id)) {
            return $redirect;
        }

        $customerReturn->delete();

        return redirect()->route('customer-returns.index')
            ->with('status', 'Return deleted.');
    }

    public function searchSales(Request $request): JsonResponse
    {
        $term = $request->query('q', '');

        $sales = Sale::query()
            ->where('status', 'completed')
            ->with(['shop:id,name', 'customerAccount:id,name'])
            ->when($shopId = $this->scopedShopId(), fn ($q) => $q->where('shop_id', $shopId))
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('receipt_number', 'like', "%{$term}%")
                        ->orWhere('customer_name', 'like', "%{$term}%")
                        ->orWhere('vehicle_plate', 'like', "%{$term}%")
                        ->orWhereHas('customerAccount', fn ($a) => $a->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest('sold_at')
            ->limit(25)
            ->get(['id', 'receipt_number', 'shop_id', 'customer_account_id', 'customer_name', 'vehicle_plate', 'total', 'sold_at', 'sale_type']);

        return response()->json(
            $sales->map(fn (Sale $sale) => [
                'id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'shop' => $sale->shop?->name,
                'customer' => $sale->customerAccount?->name ?? $sale->customer_name ?? 'Walk-in',
                'vehicle_plate' => $sale->vehicle_plate,
                'total' => (float) $sale->total,
                'sold_at' => $sale->sold_at?->format('d M Y'),
                'sale_type' => $sale->saleTypeLabel(),
            ])
        );
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

        $sale->load(['items.product.unit', 'customerAccount']);

        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'shop' => $sale->shop?->name,
                'customer' => $sale->customerAccount?->name ?? $sale->customer_name,
                'vehicle_plate' => $sale->vehicle_plate,
                'sale_type' => $sale->saleTypeLabel(),
            ],
            'items' => $sale->items->map(function ($item) use ($sale) {
                $sold = (float) $item->quantity;
                $returned = ReturnRecord::returnedQuantityForSaleProduct($sale->id, $item->product_id);
                $remaining = max(0, $sold - $returned);

                return [
                    'product_id' => $item->product_id,
                    'part_number' => $item->product->part_number,
                    'name' => $item->product->name,
                    'sold_quantity' => $sold,
                    'already_returned' => $returned,
                    'remaining_quantity' => $remaining,
                    'unit_price' => (float) $item->unit_price,
                    'unit' => $item->product->unit?->abbreviation,
                    'returnable' => $remaining > 0,
                ];
            })->values(),
        ]);
    }

    private function baseQuery(Request $request)
    {
        $query = ReturnRecord::query()
            ->where('type', 'customer')
            ->with(['shop', 'sale.customerAccount', 'items'])
            ->withCount('items');

        if ($shopId = $this->scopedShopId()) {
            $query->where('shop_id', $shopId);
        }

        return $query
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($inner) use ($term) {
                    $inner->where('return_number', 'like', "%{$term}%")
                        ->orWhere('reason', 'like', "%{$term}%")
                        ->orWhereHas('sale', fn ($s) => $s->where('receipt_number', 'like', "%{$term}%"));
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status));
    }

    private function stats(): array
    {
        $query = ReturnRecord::where('type', 'customer');

        if ($shopId = $this->scopedShopId()) {
            $query->where('shop_id', $shopId);
        }

        return [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'refunds' => (float) (clone $query)->where('status', 'completed')->sum('refund_amount'),
        ];
    }

    /**
     * @return list<array{key: string, label: string, icon: string, count: int}>
     */
    private function pipeline(array $stats): array
    {
        return [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'count' => $stats['draft']],
            ['key' => 'pending', 'label' => 'Awaiting Approval', 'icon' => 'fa-hourglass-half', 'count' => $stats['pending']],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => 'fa-circle-check', 'count' => $stats['completed']],
            ['key' => 'rejected', 'label' => 'Rejected', 'icon' => 'fa-circle-xmark', 'count' => $stats['rejected']],
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
