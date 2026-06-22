<x-app-layout :title="$employee->fullName()">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        :title="'Edit: ' . $employee->fullName()"
        subtitle="Update employee details and salary."
        icon="fa-id-badge"
        card-title="Employee Details"
        :back-url="route('employees.show', $employee)"
        :action="route('employees.update', $employee)"
        method="PUT"
        submit-label="Save Changes"
    >
        <x-employee.form-fields :employee="$employee" :shops="$shops" :warehouses="$warehouses" :roles="$roles" />
        <x-slot:guide>
            <x-employee.form-guide :is-edit="true" />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
