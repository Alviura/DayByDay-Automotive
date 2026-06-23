<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Services\LocationOverviewService;
use App\Services\WarehouseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(
        private LocationOverviewService $overview,
        private WarehouseAccessService $warehouseAccess,
    ) {
        $this->middleware('permission:warehouses.view')->only(['index', 'show']);
        $this->middleware('permission:warehouses.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $scopedWarehouseId = $this->warehouseAccess->scopedWarehouseId(auth()->user());

        $baseQuery = Warehouse::query()->when($scopedWarehouseId, fn ($q) => $q->whereKey($scopedWarehouseId));

        $warehouses = (clone $baseQuery)
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->code, fn ($q) => $q->where('code', $request->code))
            ->when($request->address, fn ($q) => $q->where('address', $request->address))
            ->when($request->sort === 'code', fn ($q) => $q->orderBy('code'))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['code', 'name', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive' => (clone $baseQuery)->where('is_active', false)->count(),
            'with_address' => (clone $baseQuery)->whereNotNull('address')->where('address', '!=', '')->count(),
        ];

        $codes = (clone $baseQuery)->orderBy('code')->pluck('code', 'code');
        $addresses = (clone $baseQuery)->whereNotNull('address')->where('address', '!=', '')->orderBy('address')->pluck('address', 'address');

        return view('warehouses.index', compact('warehouses', 'stats', 'codes', 'addresses'));
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        Warehouse::create([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('warehouses.index')->with('status', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse): View|RedirectResponse
    {
        $scopedWarehouseId = $this->warehouseAccess->scopedWarehouseId(auth()->user());
        if ($scopedWarehouseId && (int) $warehouse->id !== $scopedWarehouseId) {
            return redirect()->route('warehouses.index')
                ->with('error', 'You can only view your assigned warehouse.');
        }

        $warehouse->loadCount(['users', 'stockBalances'])
            ->load(['users' => fn ($q) => $q->orderBy('name')->limit(10)]);

        $inventory = $this->overview->inventoryContext($warehouse);
        $activity = $this->overview->warehouseActivity($warehouse);

        return view('warehouses.show', array_merge(
            compact('warehouse'),
            $inventory,
            $activity
        ));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('warehouses.index')->with('status', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->users()->exists()) {
            return back()->with('error', 'Cannot delete a warehouse that has assigned users. Reassign them first.');
        }

        if ($warehouse->stockBalances()->where('quantity_on_hand', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete a warehouse that still holds stock.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('status', 'Warehouse deleted successfully.');
    }
}
