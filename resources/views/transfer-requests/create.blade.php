<x-app-layout title="New Transfer Request">
    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    <x-module.form-page
        title="New Transfer Request"
        :subtitle="$isAdmin ? 'Create a stock request on behalf of any shop.' : 'Request stock from warehouse or another shop for '.$lockedShop->name.'.'"
        icon="fa-inbox"
        card-title="Request Details"
        :back-url="route('transfer-requests.index')"
        :action="route('transfer-requests.store')"
        submit-label="Save Draft"
    >
        <x-transfer-request.form-fields
            :warehouses="$warehouses"
            :shops="$shops"
            :destination-shops="$destinationShops"
            :products="$products"
            :prefill="$prefill"
            :locked-shop="$lockedShop"
        />

        <x-slot:guide>
            <x-transfer-request.form-guide />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
