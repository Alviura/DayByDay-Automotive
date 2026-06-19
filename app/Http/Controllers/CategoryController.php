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
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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

        return redirect()->route('product-catalog.index', ['view' => 'categories'])
            ->with('status', 'Category created successfully.');
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

        return redirect()->route('product-catalog.index', ['view' => 'categories'])
            ->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has sub-categories. Remove or reassign children first.');
        }

        $category->delete();

        return redirect()->route('product-catalog.index', ['view' => 'categories'])
            ->with('status', 'Category deleted successfully.');
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
