<x-app-layout title="New Warehouse">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Warehouse"
        subtitle="Add a storage location with a unique code, contact details, and status."
        icon="fa-warehouse"
        card-title="Warehouse Details"
        :back-url="route('warehouses.index')"
        :action="route('warehouses.store')"
        method="POST"
        submit-label="Create Warehouse"
    >
        <x-warehouse.form-fields />

        <x-slot:guide>
            <x-warehouse.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
