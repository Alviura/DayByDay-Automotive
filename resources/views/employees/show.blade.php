<x-app-layout :title="$employee->fullName()">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    @php $salary = $employee->currentSalary; @endphp

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-id-badge"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900">{{ $employee->fullName() }}</h1>
                        @if ($employee->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $employee->employee_number }} · {{ $employee->job_title }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('employees.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @can('employees.manage')
                    <a href="{{ route('employees.edit', $employee) }}" class="mi-btn-orange"><i class="fas fa-pen text-xs"></i> Edit</a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div><p class="mi-kpi-label">Gross Salary</p><p class="mi-kpi-value text-status">KES {{ $salary ? number_format($salary->grossPay(), 0) : '—' }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div><p class="mi-kpi-label">Station</p><p class="mi-kpi-value text-status text-base">{{ $employee->stationLabel() }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-map-pin"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div><p class="mi-kpi-label">Employment</p><p class="mi-kpi-value text-status text-base capitalize">{{ $employee->employment_type }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-briefcase"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div><p class="mi-kpi-label">System Access</p><p class="mi-kpi-value orange text-base">{{ $employee->hasSystemAccess() ? 'Yes' : 'No' }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-key"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="mi-card p-5 space-y-3">
                <h2 class="font-semibold text-gray-800">Contact & IDs</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-400">Phone</dt><dd>{{ $employee->phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Email</dt><dd>{{ $employee->email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">National ID</dt><dd>{{ $employee->national_id ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">KRA PIN</dt><dd>{{ $employee->kra_pin ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">NSSF</dt><dd>{{ $employee->nssf_number ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">SHIF</dt><dd>{{ $employee->shif_number ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-400">Hire Date</dt><dd>{{ $employee->hire_date?->format('d M Y') ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="mi-card p-5 space-y-3">
                <h2 class="font-semibold text-gray-800">Current Salary</h2>
                @if ($salary)
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-gray-400">Basic</dt><dd>KES {{ number_format($salary->basic_salary, 2) }}</dd></div>
                        <div><dt class="text-gray-400">Housing</dt><dd>KES {{ number_format($salary->housing_allowance, 2) }}</dd></div>
                        <div><dt class="text-gray-400">Transport</dt><dd>KES {{ number_format($salary->transport_allowance, 2) }}</dd></div>
                        <div><dt class="text-gray-400">Other</dt><dd>KES {{ number_format($salary->other_allowance, 2) }}</dd></div>
                        <div><dt class="text-gray-400">Payment</dt><dd class="capitalize">{{ $salary->payment_method }}</dd></div>
                        <div><dt class="text-gray-400">Bank</dt><dd>{{ $salary->bank_name ?: '—' }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm text-gray-400">No salary record.</p>
                @endif
            </div>
        </div>

        @if ($employee->user)
            <div class="mi-card p-5">
                <h2 class="font-semibold text-gray-800 mb-2">Linked System User</h2>
                <p class="text-sm text-gray-600">{{ $employee->user->email }} — {{ $employee->user->roles->pluck('name')->join(', ') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
