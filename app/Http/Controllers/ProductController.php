<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductName;
use App\Models\Unit;
use App\Models\VehicleMake;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
        $this->middleware('permission:products.view')->only(['index', 'show', 'search']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.edit')->only(['edit', 'update']);
        $this->middleware('permission:products.archive')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'unit', 'vehicleMake', 'productName'])
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->vehicle_make_id, fn ($q) => $q->where('vehicle_make_id', $request->vehicle_make_id))
            ->when($request->sort === 'part_number', fn ($q) => $q->orderBy('part_number'))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'price', fn ($q) => $q->orderByDesc('min_selling_price'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['part_number', 'name', 'price', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'priced' => Product::where('min_selling_price', '>', 0)->count(),
        ];

        $categories = Category::active()->with('parent')->orderBy('name')->get();
        $makes = VehicleMake::active()->orderBy('name')->get(['id', 'name']);

        return view('products.index', compact('products', 'stats', 'categories', 'makes'));
    }

    public function create(): View
    {
        return view('products.create', $this->formLookups());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($this->productAttributes($request));
        $this->syncFitment($product, $request);

        return redirect()->route('products.index')->with('status', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load([
            'productName',
            'vehicleMake',
            'vehicleModel',
            'category.parent',
            'unit',
            'fitmentModels.make',
        ]);

        $stockContext = $this->inventory->productShowContext($product);

        return view('products.show', array_merge(
            compact('product'),
            $stockContext
        ));
    }

    public function edit(Product $product): View
    {
        $product->load('fitmentModels');

        return view('products.edit', array_merge(
            ['product' => $product],
            $this->formLookups()
        ));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($this->productAttributes($request));
        $this->syncFitment($product, $request);

        return redirect()->route('products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $products = Product::query()
            ->active()
            ->search($request->q)
            ->with(['unit:id,name,abbreviation', 'category:id,name'])
            ->orderBy('name')
            ->limit(20)
            ->get([
                'id', 'part_number', 'name',
                'min_selling_price', 'max_selling_price', 'unit_id', 'category_id',
            ]);

        return response()->json($products);
    }

    private function formLookups(): array
    {
        $makes = VehicleMake::active()
            ->orderBy('name')
            ->with(['models' => fn ($q) => $q->active()->orderBy('name')])
            ->get();

        $modelsByMake = $makes->mapWithKeys(
            fn ($make) => [$make->id => $make->models->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->values()]
        );

        $allModels = $makes->flatMap(fn ($make) => $make->models->map(
            fn ($model) => ['id' => $model->id, 'name' => $make->name.' '.$model->name, 'make_id' => $make->id]
        ))->values();

        return [
            'productNames' => ProductName::active()->orderBy('name')->get(),
            'makes' => $makes,
            'modelsByMake' => $modelsByMake,
            'allModels' => $allModels,
            'categories' => Category::active()->with('parent')->orderBy('name')->get(),
            'units' => Unit::active()->orderBy('name')->get(),
        ];
    }

    private function productAttributes(StoreProductRequest|UpdateProductRequest $request): array
    {
        return [
            'part_number' => strtoupper(trim($request->part_number)),
            'name' => $request->name,
            'product_name_id' => $request->product_name_id,
            'vehicle_make_id' => $request->vehicle_make_id,
            'vehicle_model_id' => $request->vehicle_model_id,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'cost_price' => $request->cost_price ?? 0,
            'min_selling_price' => $request->min_selling_price ?? 0,
            'max_selling_price' => $request->max_selling_price ?? 0,
            'reorder_level' => $request->reorder_level ?? 0,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function syncFitment(Product $product, StoreProductRequest|UpdateProductRequest $request): void
    {
        $fitmentIds = collect($request->vehicle_model_ids ?? [])
            ->filter()
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $request->vehicle_model_id)
            ->values()
            ->all();

        $product->fitmentModels()->sync($fitmentIds);
    }
}
