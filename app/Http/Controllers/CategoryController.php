<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
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

        return view('categories.index', compact('categories', 'tree', 'stats', 'parents'));
    }

    public function create(Request $request): View
    {
        $parentOptions = $this->parentOptions();
        $selectedParentId = $request->query('parent_id');

        return view('categories.create', compact('parentOptions', 'selectedParentId'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('categories.index')->with('status', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parentOptions = $this->parentOptions($category);

        return view('categories.edit', compact('category', 'parentOptions'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('categories.index')->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has sub-categories. Remove or reassign children first.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Category deleted successfully.');
    }

    private function parentOptions(?Category $exclude = null)
    {
        $query = Category::with('parent')->orderBy('name');

        if ($exclude) {
            $query->whereNotIn('id', array_merge([$exclude->id], $exclude->getDescendantIds()));
        }

        return $query->get();
    }
}
