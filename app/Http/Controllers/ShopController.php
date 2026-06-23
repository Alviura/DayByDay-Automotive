<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use App\Services\LocationOverviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(private LocationOverviewService $overview)
    {
        $this->middleware('permission:shops.view')->only(['index', 'show']);
        $this->middleware('permission:shops.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $shops = Shop::query()
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
            'total' => Shop::count(),
            'active' => Shop::where('is_active', true)->count(),
            'inactive' => Shop::where('is_active', false)->count(),
            'with_address' => Shop::whereNotNull('address')->where('address', '!=', '')->count(),
        ];

        $codes = Shop::orderBy('code')->pluck('code', 'code');
        $addresses = Shop::whereNotNull('address')->where('address', '!=', '')->orderBy('address')->pluck('address', 'address');

        return view('shops.index', compact('shops', 'stats', 'codes', 'addresses'));
    }

    public function create(): View
    {
        return view('shops.create');
    }

    public function store(StoreShopRequest $request): RedirectResponse
    {
        Shop::create([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('shops.index')->with('status', 'Shop created successfully.');
    }

    public function show(Shop $shop): View
    {
        $shop->loadCount(['users', 'stockBalances'])
            ->load(['users' => fn ($q) => $q->orderBy('name')->limit(10)]);

        $inventory = $this->overview->inventoryContext($shop);
        $activity = $this->overview->shopActivity($shop);

        return view('shops.show', array_merge(
            compact('shop'),
            $inventory,
            $activity
        ));
    }

    public function edit(Shop $shop): View
    {
        return view('shops.edit', compact('shop'));
    }

    public function update(UpdateShopRequest $request, Shop $shop): RedirectResponse
    {
        $shop->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('shops.index')->with('status', 'Shop updated successfully.');
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        if ($shop->users()->exists()) {
            return back()->with('error', 'Cannot delete a shop that has assigned users. Reassign them first.');
        }

        if ($shop->stockBalances()->where('quantity_on_hand', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete a shop that still holds stock.');
        }

        $shop->delete();

        return redirect()->route('shops.index')->with('status', 'Shop deleted successfully.');
    }
}
