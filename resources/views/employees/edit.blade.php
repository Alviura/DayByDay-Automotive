<x-app-layout :title="'Edit: ' . $employee->fullName()">
    @push('styles')
        <x-module.page-index-styles />
        @include('employees.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="emp-avatar {{ $employee->is_active ? '' : 'inactive' }}">{{ $employee->initials() }}</div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $employee->fullName() }}</h1>
                        <span class="{{ $employee->statusBadgeClass() }}">{{ $employee->statusLabel() }}</span>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">
                        <span class="emp-mono">{{ $employee->employee_number }}</span> · Update employee details and salary
                    </p>
                </div>
            </div>
            <a href="{{ route('employees.show', $employee) }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Profile
            </a>
        </div>

        @include('employees.partials.nav-tabs')

        <div class="mi-form-split">
            <div class="mi-card mi-form-main">
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                    <div class="mi-form-body">
                        <x-employee.form-fields :employee="$employee" :shops="$shops" :warehouses="$warehouses" :roles="$roles" />
                    </div>
                    <div class="mi-form-actions">
                        <a href="{{ route('employees.show', $employee) }}" class="mi-btn-ghost">Cancel</a>
                        <button type="submit" class="mi-btn-orange">
                            <i class="fas fa-check text-xs"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
            <x-employee.form-guide :is-edit="true" />
        </div>
    </div>
</x-app-layout>
