<x-app-layout title="New Employee">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <x-module.form-page
        title="New Employee"
        subtitle="Add staff record with monthly salary. System login is optional."
        icon="fa-id-badge"
        card-title="Employee Details"
        :back-url="route('employees.index')"
        :action="route('employees.store')"
        method="POST"
        submit-label="Create Employee"
    >
        <x-employee.form-fields :shops="$shops" :warehouses="$warehouses" :roles="$roles" />
        <x-slot:guide>
            <x-employee.form-guide />
        </x-slot:guide>
    </x-module.form-page>
</x-app-layout>
