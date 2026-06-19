<x-app-layout title="New Category">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Category"
        subtitle="Add a category or sub-category to organise products."
        icon="fa-folder-tree"
        card-title="Category Details"
        :back-url="route('product-catalog.index', ['view' => 'categories'])"
        :action="route('categories.store')"
        method="POST"
        submit-label="Create Category"
    >
        <x-category.form-fields :parent-options="$parentOptions" :selected-parent-id="$selectedParentId" />

        <x-slot:guide>
            <x-category.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
