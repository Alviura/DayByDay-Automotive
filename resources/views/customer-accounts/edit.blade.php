<x-app-layout title="Edit Fleet Account">
    @push('styles')
        <x-module.page-index-styles />
        @include('customer-accounts.partials.page-styles')
    @endpush

    <x-module.form-page
        title="Edit Fleet Account"
        :subtitle="'Update details for ' . $customerAccount->name . '.'"
        icon="fa-bus"
        card-title="Account Details"
        :back-url="route('customer-accounts.show', $customerAccount)"
        :cancel-url="route('customer-accounts.show', $customerAccount)"
        :action="route('customer-accounts.update', $customerAccount)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-customer-account.form-fields :customer-account="$customerAccount" />

        <x-slot:cardMeta>
            <span class="ca-badge {{ $customerAccount->is_active ? 'ca-badge-active' : 'ca-badge-inactive' }}">
                {{ $customerAccount->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-customer-account.form-guide :is-edit="true" :customer-account="$customerAccount" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
