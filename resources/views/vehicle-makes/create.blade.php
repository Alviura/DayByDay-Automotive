<x-app-layout title="New Vehicle Make">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Vehicle Make"
        subtitle="Add a manufacturer or brand for grouping vehicle models."
        icon="fa-car-side"
        card-title="Make Details"
        :back-url="route('vehicle-catalog.index', ['view' => 'makes'])"
        :action="route('vehicle-makes.store')"
        method="POST"
        submit-label="Create Make"
    >
        <x-vehicle-make.form-fields />

        <x-slot:guide>
            <x-vehicle-make.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
