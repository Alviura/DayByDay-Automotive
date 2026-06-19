<x-app-layout title="New Transfer Request">
    @push('styles')<x-module.page-index-styles />@endpush
    <x-module.form-page
        title="New Transfer Request"
        subtitle="Request stock movement between warehouse and shop or between shops."
        icon="fa-right-left"
        card-title="Request Details"
        :back-url="route('transfer-requests.index')"
        :action="route('transfer-requests.store')"
        submit-label="Save Draft"
    >
        <x-transfer-request.form-fields :warehouses="$warehouses" :shops="$shops" :products="$products" />
    </x-module.form-page>
</x-app-layout>
