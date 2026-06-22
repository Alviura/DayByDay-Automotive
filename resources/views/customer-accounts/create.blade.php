<x-app-layout title="New Fleet Account">
    @push('styles')
        <x-module.page-index-styles />
        @include('customer-accounts.partials.page-styles')
    @endpush

    <x-module.form-page
        title="New Fleet Account"
        subtitle="Set up a credit customer for monthly fleet billing."
        icon="fa-bus"
        card-title="Account Details"
        :back-url="route('customer-accounts.index')"
        :action="route('customer-accounts.store')"
        method="POST"
        submit-label="Create Account"
    >
        <x-customer-account.form-fields />

        <x-slot:guide>
            <x-customer-account.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
