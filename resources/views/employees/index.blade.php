<x-app-layout title="Employees">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-id-badge"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Employees</h1>
                    <p class="mt-0.5 text-sm text-gray-500">HR master — staff records, stations, and monthly salary.</p>
                </div>
            </div>
            @can('employees.manage')
                <a href="{{ route('employees.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> Add Employee
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value">{{ number_format($stats['total']) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div><p class="mi-kpi-label">Active</p><p class="mi-kpi-value">{{ number_format($stats['active']) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div><p class="mi-kpi-label">On Payroll</p><p class="mi-kpi-value">{{ number_format($stats['on_payroll']) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-money-check-dollar"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div><p class="mi-kpi-label">With Login</p><p class="mi-kpi-value orange">{{ number_format($stats['with_login']) }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-key"></i></div>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <span class="text-sm font-semibold text-gray-700"><i class="fas fa-sliders text-gray-400 text-sm"></i> Filters</span>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">Toggle</button>
            </div>
            <form method="GET" x-show="filtersOpen" class="p-4 border-t border-gray-100">
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Name, number, job title…">
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Station</label>
                        <select name="station" class="mi-select">
                            <option value="">All</option>
                            @foreach (['shop' => 'Shop', 'warehouse' => 'Warehouse', 'field' => 'Field', 'head_office' => 'Head Office'] as $v => $l)
                                <option value="{{ $v }}" @selected(request('station') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="submit" class="mi-btn-orange text-sm">Apply</button>
                    <a href="{{ route('employees.index') }}" class="mi-btn-ghost text-sm">Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Job Title</th>
                            <th>Station</th>
                            <th>Gross Salary</th>
                            <th>Login</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900">{{ $employee->fullName() }}</div>
                                    <div class="text-xs text-gray-400">{{ $employee->employee_number }}</div>
                                </td>
                                <td>{{ $employee->job_title }}</td>
                                <td>{{ $employee->stationLabel() }}</td>
                                <td>
                                    @if ($employee->currentSalary)
                                        KES {{ number_format($employee->currentSalary->grossPay(), 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($employee->hasSystemAccess())
                                        <span class="mi-status-active text-xs">Yes</span>
                                    @else
                                        <span class="text-gray-400 text-xs">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($employee->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('employees.show', $employee) }}" class="mi-btn-ghost text-xs">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-8 text-gray-400">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($employees->hasPages())
                <div class="p-4 border-t border-gray-100">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
