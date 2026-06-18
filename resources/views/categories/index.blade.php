<x-app-layout title="Categories">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: 'tree' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-folder-tree"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Categories</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Organise products in a nested category tree.</p>
                </div>
            </div>
            @can('master-data.manage')
                <a href="{{ route('categories.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i>
                    Add Category
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Categories</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-folder-tree"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Active</p>
                    <p class="mi-kpi-value">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Inactive</p>
                    <p class="mi-kpi-value">{{ number_format($stats['inactive']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-pause-circle"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Top Level</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['top_level']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-folder"></i></div>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            <form method="GET" x-show="filtersOpen" x-transition>
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Category name…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-sitemap"></i> Parent</label>
                        <select name="parent_id" class="mi-select">
                            <option value="">All categories</option>
                            <option value="root" @selected(request('parent_id') === 'root')>Top level only</option>
                            @foreach ($parents as $id => $name)
                                <option value="{{ $id }}" @selected(request('parent_id') == $id)>Under {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-toggle-on"></i> Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="name" @selected(request('sort') === 'name')>Name (A–Z)</option>
                            <option value="children" @selected(request('sort') === 'children')>Most sub-categories</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('categories.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Reset All
                    </a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    @if (request()->hasAny(['search', 'parent_id', 'status', 'sort']))
                        Filtered list — <strong class="text-gray-700">{{ $categories->total() }}</strong> results
                    @else
                        <strong class="text-gray-700">{{ $stats['total'] }}</strong> categories in hierarchy
                    @endif
                </p>
                <div class="mi-view-toggle">
                    <button type="button" :class="{ 'active': viewMode === 'tree' }" @click="viewMode = 'tree'" title="Tree view">
                        <i class="fas fa-folder-tree"></i>
                    </button>
                    <button type="button" :class="{ 'active': viewMode === 'list' }" @click="viewMode = 'list'" title="List view">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            {{-- Tree view --}}
            <div x-show="viewMode === 'tree'" class="mi-tree-wrap">
                @forelse ($tree as $root)
                    <x-category.tree-node :category="$root" />
                @empty
                    <div class="py-12 text-center text-gray-400">
                        <i class="fas fa-folder-tree mb-2 block text-3xl text-gray-200"></i>
                        <p class="font-medium text-gray-600">No categories yet</p>
                    </div>
                @endforelse
            </div>

            {{-- List view --}}
            <div x-show="viewMode === 'list'" x-cloak class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Sub-categories</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="text-gray-400 font-medium">{{ $categories->firstItem() + $loop->index }}</td>
                                <td><p class="mi-pkg-name">{{ $category->name }}</p></td>
                                <td>
                                    @if ($category->parent)
                                        <span class="text-sm text-gray-500">{{ $category->parent->name }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($category->children_count > 0)
                                        <a href="{{ route('categories.index', ['parent_id' => $category->id, 'view' => 'list']) }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                                            {{ $category->children_count }}
                                        </a>
                                    @else
                                        <span class="text-gray-300">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($category->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @can('master-data.manage')
                                            <a href="{{ route('categories.create', ['parent_id' => $category->id]) }}" class="mi-action view" title="Add sub-category">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="{{ route('categories.edit', $category) }}" class="mi-action edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($category->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="mi-action del" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="!py-14 text-center text-gray-400">
                                    <i class="fas fa-folder-tree mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No categories found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="mi-card-foot" x-show="viewMode === 'list'" x-cloak>
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
