<x-app-layout title="New Unit">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Unit"
        subtitle="Add a unit of measure for products and stock."
        icon="fa-ruler-combined"
        card-title="Unit Details"
        :back-url="route('units.index')"
        :action="route('units.store')"
        method="POST"
        submit-label="Create Unit"
    >
        <x-unit.form-fields />

        <x-slot:guide>
            <x-unit.form-guide :is-edit="false" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
