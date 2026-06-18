<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductNameRequest;
use App\Http\Requests\UpdateProductNameRequest;
use App\Models\ProductName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductNameController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $productNames = ProductName::query()
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort !== 'name' && $request->sort !== 'oldest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ProductName::count(),
            'active' => ProductName::where('is_active', true)->count(),
            'inactive' => ProductName::where('is_active', false)->count(),
            'active_pct' => ProductName::count() > 0
                ? (int) round(ProductName::where('is_active', true)->count() / ProductName::count() * 100)
                : 0,
        ];

        return view('product-names.index', compact('productNames', 'stats'));
    }

    public function create(): View
    {
        return view('product-names.create');
    }

    public function store(StoreProductNameRequest $request): RedirectResponse
    {
        ProductName::create([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('product-names.index')->with('status', 'Product name created successfully.');
    }

    public function edit(ProductName $productName): View
    {
        return view('product-names.edit', compact('productName'));
    }

    public function update(UpdateProductNameRequest $request, ProductName $productName): RedirectResponse
    {
        $productName->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('product-names.index')->with('status', 'Product name updated successfully.');
    }

    public function destroy(ProductName $productName): RedirectResponse
    {
        $productName->delete();

        return redirect()->route('product-names.index')->with('status', 'Product name deleted successfully.');
    }
}
