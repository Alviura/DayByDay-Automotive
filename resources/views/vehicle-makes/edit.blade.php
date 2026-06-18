<x-app-layout title="Edit Vehicle Make">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Vehicle Make"
        :subtitle="'Update ' . $vehicleMake->name"
        icon="fa-car-side"
        card-title="Make Details"
        :back-url="route('vehicle-catalog.index', ['view' => 'makes'])"
        :action="route('vehicle-makes.update', $vehicleMake)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-vehicle-make.form-fields :vehicle-make="$vehicleMake" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $vehicleMake->is_active ? 'active' : 'inactive' }}">
                {{ $vehicleMake->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-vehicle-make.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
