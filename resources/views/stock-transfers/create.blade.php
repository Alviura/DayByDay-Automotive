<x-app-layout title="New Stock Transfer">
    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    <x-module.form-page
        title="New Stock Transfer"
        :subtitle="$lockedWarehouse ? 'Distribute stock from your warehouse.' : 'Move stock between warehouse and shops.'"
        icon="fa-right-left"
        card-title="Transfer Details"
        :back-url="route('stock-transfers.index')"
        :action="route('stock-transfers.store')"
        submit-label="Save Draft"
    >
        <x-transfer.form-fields
            :warehouses="$warehouses"
            :shops="$shops"
            :products="$products"
            :prefill="$prefill"
            :allowed-types="$allowedTypes"
            :locked-warehouse="$lockedWarehouse"
            availability-url="{{ route('stock-transfers.availability') }}"
        />

        <x-slot:guide>
            <x-transfer.form-guide :for-warehouse-manager="(bool) $lockedWarehouse" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
