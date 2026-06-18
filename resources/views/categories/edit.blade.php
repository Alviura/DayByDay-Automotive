<x-app-layout title="Edit Category">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Category"
        :subtitle="$category->name"
        icon="fa-folder-tree"
        card-title="Category Details"
        :back-url="route('categories.index')"
        :action="route('categories.update', $category)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-category.form-fields :category="$category" :parent-options="$parentOptions" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $category->is_active ? 'active' : 'inactive' }}">
                {{ $category->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-category.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
