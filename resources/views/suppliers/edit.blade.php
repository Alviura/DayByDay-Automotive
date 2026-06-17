<x-app-layout title="Edit Supplier">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Supplier"
        :subtitle="'Update details for ' . $supplier->name . ' (' . $supplier->code . ').'"
        icon="fa-truck"
        card-title="Supplier Details"
        :back-url="route('suppliers.index')"
        :action="route('suppliers.update', $supplier)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-supplier.form-fields :supplier="$supplier" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $supplier->is_active ? 'active' : 'inactive' }}">
                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-supplier.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
