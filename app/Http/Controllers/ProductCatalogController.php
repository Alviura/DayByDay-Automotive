<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductName;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
    }

    public function index(Request $request): View
    {
        $view = match ($request->query('view')) {
            'units' => 'units',
            'categories' => 'categories',
            default => 'names',
        };

        if ($view === 'categories') {
            return $this->categoriesView($request, $view);
        }

        if ($view === 'units') {
            return $this->unitsView($request, $view);
        }

        return $this->productNamesView($request, $view);
    }

    private function categoriesView(Request $request, string $view): View
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount('children')
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->parent_id === 'root', fn ($q) => $q->whereNull('parent_id'))
            ->when($request->parent_id && $request->parent_id !== 'root', fn ($q) => $q->where('parent_id', $request->parent_id))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'children', fn ($q) => $q->orderByDesc('children_count'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['name', 'children', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $tree = Category::with(['children' => fn ($q) => $q->with('children')->orderBy('name')])
            ->roots()
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'top_level' => Category::whereNull('parent_id')->count(),
        ];

        $parents = Category::orderBy('name')->pluck('name', 'id');

        return view('product-catalog.index', compact('view', 'categories', 'tree', 'stats', 'parents'));
    }

    private function unitsView(Request $request, string $view): View
    {
        $units = Unit::query()
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'abbreviation', fn ($q) => $q->orderBy('abbreviation'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['name', 'abbreviation', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Unit::count(),
            'active' => Unit::where('is_active', true)->count(),
            'inactive' => Unit::where('is_active', false)->count(),
            'related' => ProductName::count(),
            'with_abbreviation' => Unit::whereNotNull('abbreviation')->where('abbreviation', '!=', '')->count(),
        ];

        return view('product-catalog.index', compact('view', 'units', 'stats'));
    }

    private function productNamesView(Request $request, string $view): View
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
            'related' => Unit::count(),
            'active_pct' => ProductName::count() > 0
                ? (int) round(ProductName::where('is_active', true)->count() / ProductName::count() * 100)
                : 0,
        ];

        return view('product-catalog.index', compact('view', 'productNames', 'stats'));
    }
}
