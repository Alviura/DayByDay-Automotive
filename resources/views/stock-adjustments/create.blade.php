<x-app-layout title="New Stock Adjustment">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Stock Adjustment"
        subtitle="Record a physical count variance and submit for approval before posting to the ledger."
        icon="fa-boxes-stacked"
        card-title="Adjustment Details"
        :back-url="route('stock-adjustments.index')"
        :action="route('stock-adjustments.store')"
        method="POST"
        submit-label="Save Draft"
    >
        <x-stock-adjustment.form-fields :warehouses="$warehouses" :shops="$shops" :products="$products" />
    </x-module.form-page>
</x-app-layout>
