<x-app-layout title="New Vehicle Model">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Vehicle Model"
        subtitle="Add a model under an existing vehicle make."
        icon="fa-car"
        card-title="Model Details"
        :back-url="route('vehicle-models.index')"
        :action="route('vehicle-models.store')"
        method="POST"
        submit-label="Create Model"
    >
        <x-vehicle-model.form-fields :makes="$makes" :selected-make-id="$selectedMakeId" />

        <x-slot:guide>
            <x-vehicle-model.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
