<x-app-layout title="New Stock Adjustment">
    @push('styles')
        <x-module.page-index-styles />
        @include('stock-adjustments.partials.page-styles')
    @endpush

    <x-module.form-page
        title="New Stock Adjustment"
        subtitle="Record a physical count variance and submit for approval before posting to the ledger."
        icon="fa-sliders"
        card-title="Adjustment"
        :back-url="route('stock-adjustments.index')"
        :action="route('stock-adjustments.store')"
        method="POST"
        submit-label="Save Draft"
    >
        <x-stock-adjustment.form-fields :warehouses="$warehouses" :shops="$shops" :products="$products" :locked-warehouse="$lockedWarehouse ?? null" />

        <x-slot:guide>
            <x-stock-adjustment.form-guide />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
