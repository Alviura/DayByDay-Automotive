<x-app-layout title="New Product Name">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Product Name"
        subtitle="Add a generic part type name for the product catalogue."
        icon="fa-tags"
        card-title="Product Name Details"
        :back-url="route('product-names.index')"
        :action="route('product-names.store')"
        method="POST"
        submit-label="Create Product Name"
    >
        <x-product-name.form-fields />

        <x-slot:guide>
            <x-product-name.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
