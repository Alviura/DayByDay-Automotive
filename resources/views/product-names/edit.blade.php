<x-app-layout title="Edit Product Name">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Product Name"
        :subtitle="$productName->name"
        icon="fa-tags"
        card-title="Product Name Details"
        :back-url="route('product-names.index')"
        :action="route('product-names.update', $productName)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-product-name.form-fields :product-name="$productName" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $productName->is_active ? 'active' : 'inactive' }}">
                {{ $productName->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-product-name.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
