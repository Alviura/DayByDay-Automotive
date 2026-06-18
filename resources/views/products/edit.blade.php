<x-app-layout title="Edit Product">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Product"
        subtitle="Update catalogue details, fitment, and pricing for {{ $product->part_number }}."
        icon="fa-car-side"
        card-title="Product Details"
        :back-url="route('products.index')"
        :action="route('products.update', $product)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-product.form-fields
            :product="$product"
            :product-names="$productNames"
            :makes="$makes"
            :models-by-make="$modelsByMake"
            :all-models="$allModels"
            :categories="$categories"
            :units="$units"
            :suppliers="$suppliers"
        />

        <x-slot:guide>
            <x-product.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
