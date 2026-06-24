<x-app-layout title="New Employee">
    @push('styles')
        <x-module.page-index-styles />
        @include('employees.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-user-plus"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">New Employee</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Add a staff record with monthly salary. System login is optional.</p>
                </div>
            </div>
            <a href="{{ route('employees.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </div>

        @include('employees.partials.nav-tabs', ['active' => 'create'])

        <div class="mi-form-split">
            <div class="mi-card mi-form-main">
                <form method="POST" action="{{ route('employees.store') }}">
                    @csrf
                    <div class="mi-form-body">
                        <x-employee.form-fields :shops="$shops" :warehouses="$warehouses" :roles="$roles" />
                    </div>
                    <div class="mi-form-actions">
                        <a href="{{ route('employees.index') }}" class="mi-btn-ghost">Cancel</a>
                        <button type="submit" class="mi-btn-orange">
                            <i class="fas fa-plus text-xs"></i> Create Employee
                        </button>
                    </div>
                </form>
            </div>
            <x-employee.form-guide />
        </div>
    </div>
</x-app-layout>
