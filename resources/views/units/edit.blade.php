<x-app-layout title="Edit Unit">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Unit"
        :subtitle="$unit->name"
        icon="fa-ruler-combined"
        card-title="Unit Details"
        :back-url="route('product-catalog.index', ['view' => 'units'])"
        :action="route('units.update', $unit)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-unit.form-fields :unit="$unit" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $unit->is_active ? 'active' : 'inactive' }}">
                {{ $unit->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-unit.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
