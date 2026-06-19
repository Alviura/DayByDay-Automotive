<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductNameRequest;
use App\Http\Requests\UpdateProductNameRequest;
use App\Models\ProductName;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductNameController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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

        return redirect()->route('product-catalog.index', ['view' => 'names'])
            ->with('status', 'Product name created successfully.');
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

        return redirect()->route('product-catalog.index', ['view' => 'names'])
            ->with('status', 'Product name updated successfully.');
    }

    public function destroy(ProductName $productName): RedirectResponse
    {
        $productName->delete();

        return redirect()->route('product-catalog.index', ['view' => 'names'])
            ->with('status', 'Product name deleted successfully.');
    }
}
