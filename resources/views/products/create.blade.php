<x-app-layout title="New Product">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Product"
        subtitle="Add a part to the catalogue with fitment, pricing, and supplier links."
        icon="fa-car-side"
        card-title="Product Details"
        :back-url="route('products.index')"
        :action="route('products.store')"
        method="POST"
        submit-label="Create Product"
    >
        <x-product.form-fields
            :product-names="$productNames"
            :makes="$makes"
            :models-by-make="$modelsByMake"
            :all-models="$allModels"
            :categories="$categories"
            :units="$units"
            :suppliers="$suppliers"
        />

        <x-slot:guide>
            <x-product.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
