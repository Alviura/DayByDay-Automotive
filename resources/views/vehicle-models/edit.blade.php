<x-app-layout title="Edit Vehicle Model">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="Edit Vehicle Model"
        :subtitle="$vehicleModel->make->name . ' ' . $vehicleModel->name"
        icon="fa-car"
        card-title="Model Details"
        :back-url="route('vehicle-models.index')"
        :action="route('vehicle-models.update', $vehicleModel)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <x-vehicle-model.form-fields :vehicle-model="$vehicleModel" :makes="$makes" />

        <x-slot:cardMeta>
            <span class="mi-status-{{ $vehicleModel->is_active ? 'active' : 'inactive' }}">
                {{ $vehicleModel->is_active ? 'Active' : 'Inactive' }}
            </span>
        </x-slot:cardMeta>

        <x-slot:guide>
            <x-vehicle-model.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
