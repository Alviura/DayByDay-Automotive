@props(['category', 'depth' => 0])

<div class="mi-tree-node" style="margin-left: {{ $depth * 1.1 }}rem;">
    <div class="mi-tree-node-inner">
        <div class="flex items-center gap-2 min-w-0">
            @if ($depth > 0)
                <i class="fas fa-turn-up fa-rotate-90 text-gray-300 text-xs flex-shrink-0"></i>
            @else
                <i class="fas fa-folder text-orange-400 text-sm flex-shrink-0"></i>
            @endif
            <div class="min-w-0">
                <p class="mi-pkg-name truncate">{{ $category->name }}</p>
                @if ($category->children->isNotEmpty())
                    <p class="text-xs text-gray-400">{{ $category->children->count() }} sub-categories</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            @if ($category->is_active)
                <span class="mi-status-active">Active</span>
            @else
                <span class="mi-status-inactive">Inactive</span>
            @endif
            <div class="flex items-center gap-1">
                @can('master-data.manage')
                    <a href="{{ route('categories.create', ['parent_id' => $category->id]) }}" class="mi-action view" title="Add sub-category">
                        <i class="fas fa-plus"></i>
                    </a>
                    <a href="{{ route('categories.edit', $category) }}" class="mi-action edit" title="Edit">
                        <i class="fas fa-pen"></i>
                    </a>
                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" data-confirm="Delete {{ addslashes($category->name) }}?" data-confirm-variant="danger">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mi-action del" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
    @foreach ($category->children as $child)
        <x-category.tree-node :category="$child" :depth="$depth + 1" />
    @endforeach
</div>
