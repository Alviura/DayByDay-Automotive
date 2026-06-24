<x-app-layout title="Employees">
    @push('styles')
        <x-module.page-index-styles />
        @include('employees.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','station','employment_type','sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-id-badge"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Employees</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Staff records, work stations, salaries, and system access.</p>
                </div>
            </div>
            @can('employees.manage')
                <a href="{{ route('employees.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> Add Employee
                </a>
            @endcan
        </div>

        @include('employees.partials.nav-tabs', ['active' => 'index'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Staff</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="emp-kpi-sub">{{ number_format($stats['active']) }} active · {{ number_format($stats['inactive']) }} inactive</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">On Payroll</p>
                    <p class="mi-kpi-value">{{ number_format($stats['on_payroll']) }}</p>
                    <p class="emp-kpi-sub">Active with no termination date</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-money-check-dollar"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Monthly Gross</p>
                    <p class="mi-kpi-value emp-amt orange">{{ number_format($stats['monthly_gross'], 0) }}</p>
                    <p class="emp-kpi-sub">KES payroll cost (current salaries)</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">System Logins</p>
                    <p class="mi-kpi-value">{{ number_format($stats['with_login']) }}</p>
                    <p class="emp-kpi-sub">Linked user accounts</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-key"></i></div>
            </div>
        </div>

        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Quick filter</p>
            <div class="emp-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $statusFilter === $step['key'];
                        $params = array_merge(request()->except('page', 'status'), ['status' => $step['key']]);
                    @endphp
                    <a href="{{ route('employees.index', $params) }}" class="emp-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="emp-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="emp-pipe-count">{{ number_format($step['count']) }}</span>
                        <span class="emp-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mi-filter-bar no-print">
            <button type="button" class="mi-filter-toggle" @click="filtersOpen = !filtersOpen">
                <i class="fas fa-sliders"></i> Filters
            </button>
            <form method="GET" class="mi-filter-form" x-show="filtersOpen" x-collapse>
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <div class="mi-filter-grid">
                    <div>
                        <label class="mi-field-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input w-full" placeholder="Name, number, job title, phone…">
                    </div>
                    <div>
                        <label class="mi-field-label">Station</label>
                        <select name="station" class="mi-select w-full">
                            <option value="">All stations</option>
                            @foreach (['shop' => 'Shop', 'warehouse' => 'Warehouse', 'field' => 'Field', 'head_office' => 'Head Office'] as $v => $l)
                                <option value="{{ $v }}" @selected(request('station') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label">Employment</label>
                        <select name="employment_type" class="mi-select w-full">
                            <option value="">All types</option>
                            @foreach (['permanent' => 'Permanent', 'contract' => 'Contract', 'casual' => 'Casual'] as $v => $l)
                                <option value="{{ $v }}" @selected(request('employment_type') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label">Sort</label>
                        <select name="sort" class="mi-select w-full">
                            <option value="">Newest first</option>
                            <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">Apply</button>
                    <a href="{{ route('employees.index', ['status' => $statusFilter]) }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="emp-doc-card">
            @if ($employees->isEmpty())
                <div class="mi-empty py-16 text-center">
                    <div class="mi-empty-icon"><i class="fas fa-id-badge"></i></div>
                    <p class="font-semibold text-gray-700">No employees found</p>
                    <p class="text-sm text-gray-500 mt-1">Try adjusting filters or add your first staff member.</p>
                    @can('employees.manage')
                        <a href="{{ route('employees.create') }}" class="mi-btn-orange mt-4 inline-flex">
                            <i class="fas fa-plus text-xs"></i> Add Employee
                        </a>
                    @endcan
                </div>
            @else
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Station</th>
                            <th>Type</th>
                            <th class="text-right">Gross Salary</th>
                            <th>Login</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr class="emp-index-row" onclick="window.location='{{ route('employees.show', $employee) }}'">
                                <td>
                                    <div class="emp-person-cell">
                                        <div class="emp-avatar {{ $employee->is_active ? '' : 'inactive' }}">{{ $employee->initials() }}</div>
                                        <div class="min-w-0">
                                            <p class="emp-person-name truncate">{{ $employee->fullName() }}</p>
                                            <p class="emp-person-sub emp-mono">{{ $employee->employee_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-sm font-medium text-gray-800">{{ $employee->job_title }}</p>
                                    @if ($employee->hire_date)
                                        <p class="text-xs text-gray-400 mt-0.5">Since {{ $employee->hire_date->format('M Y') }}</p>
                                    @endif
                                </td>
                                <td>
                                    <span class="emp-station-pill {{ $employee->stationPillClass() }}">
                                        <i class="fas {{ $employee->stationIcon() }}"></i>
                                        {{ $employee->stationLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="emp-type-pill {{ $employee->employmentPillClass() }}">
                                        {{ $employee->employmentTypeLabel() }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @if ($employee->currentSalary)
                                        <span class="emp-amt">{{ number_format($employee->currentSalary->grossPay(), 2) }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($employee->hasSystemAccess())
                                        <span class="emp-login-yes"><i class="fas fa-circle-check"></i> Yes</span>
                                        @if ($employee->user?->roles->first())
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $employee->user->roles->first()->name }}</p>
                                        @endif
                                    @else
                                        <span class="emp-login-no"><i class="fas fa-minus"></i> No</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $employee->statusBadgeClass() }}">{{ $employee->statusLabel() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mi-table-footer">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
