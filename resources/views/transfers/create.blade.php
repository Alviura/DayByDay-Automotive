<x-app-layout title="New Transfer">
    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    <x-module.form-page
        title="New Stock Transfer"
        subtitle="Move stock from warehouse to shop or between shops."
        icon="fa-right-left"
        card-title="Transfer Details"
        :back-url="route('transfers.index')"
        :action="route('transfers.store')"
        submit-label="Save Draft"
    >
        <x-transfer.form-fields :warehouses="$warehouses" :shops="$shops" :products="$products" :prefill="$prefill" />

        <x-slot:guide>
            <x-transfer.form-guide />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
