<x-app-layout title="Edit Warehouse">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Warehouse"
        :subtitle="'Update details for ' . $warehouse->name . ' (' . $warehouse->code . ').'"
        icon="fa-warehouse"
        card-title="Warehouse Details"
        :back-url="route('warehouses.index')"
        :action="route('warehouses.update', $warehouse)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-warehouse.form-fields :warehouse="$warehouse" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $warehouse->is_active ? 'active' : 'inactive' }}">
                {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-warehouse.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
