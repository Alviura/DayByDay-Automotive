<x-app-layout title="New Supplier">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Supplier"
        subtitle="Add a vendor with contact details, currency, and lead-time information."
        icon="fa-truck"
        card-title="Supplier Details"
        :back-url="route('suppliers.index')"
        :action="route('suppliers.store')"
        method="POST"
        submit-label="Create Supplier"
    >
        <x-supplier.form-fields />

        <x-slot:guide>
            <x-supplier.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
