<x-app-layout title="New Shop">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Shop"
        subtitle="Add a retail location with a unique code, contact details, and status."
        icon="fa-store"
        card-title="Shop Details"
        :back-url="route('shops.index')"
        :action="route('shops.store')"
        method="POST"
        submit-label="Create Shop"
    >
        <x-shop.form-fields />

        <x-slot:guide>
            <x-shop.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
