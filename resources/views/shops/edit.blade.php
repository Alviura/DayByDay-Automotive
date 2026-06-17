<x-app-layout title="Edit Shop">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Shop"
        :subtitle="'Update details for ' . $shop->name . ' (' . $shop->code . ').'"
        icon="fa-store"
        card-title="Shop Details"
        :back-url="route('shops.index')"
        :action="route('shops.update', $shop)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-shop.form-fields :shop="$shop" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $shop->is_active ? 'active' : 'inactive' }}">
                {{ $shop->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-shop.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
