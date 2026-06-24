<x-app-layout :title="$employee->fullName()">
    @push('styles')
        <x-module.page-index-styles />
        @include('employees.partials.page-styles')
    @endpush

    @php
        $salary = $employee->currentSalary;
        $onPayroll = $employee->is_active && ! $employee->termination_date;
    @endphp

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4 no-print">
            <div class="flex items-start gap-3 min-w-0">
                <div class="emp-show-hero">
                    <div class="emp-avatar {{ $employee->is_active ? '' : 'inactive' }}">{{ $employee->initials() }}</div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $employee->fullName() }}</h1>
                            <span class="{{ $employee->statusBadgeClass() }}">{{ $employee->statusLabel() }}</span>
                            <span class="emp-type-pill {{ $employee->employmentPillClass() }}">{{ $employee->employmentTypeLabel() }}</span>
                        </div>
                        <p class="mt-0.5 text-sm text-gray-500">
                            <span class="emp-mono">{{ $employee->employee_number }}</span>
                            · {{ $employee->job_title }}
                        </p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="emp-station-pill {{ $employee->stationPillClass() }}">
                                <i class="fas {{ $employee->stationIcon() }}"></i>
                                {{ $employee->stationLabel() }}
                            </span>
                            @if ($employee->hire_date)
                                <span class="text-xs text-gray-400">Hired {{ $employee->hire_date->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="mi-btn-ghost"><i class="fas fa-print text-xs"></i> Print</button>
                <a href="{{ route('employees.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> All Staff</a>
                @can('employees.manage')
                    <a href="{{ route('employees.edit', $employee) }}" class="mi-btn-orange"><i class="fas fa-pen text-xs"></i> Edit</a>
                @endcan
            </div>
        </div>

        @include('employees.partials.nav-tabs', ['active' => 'index'])

        @if ($employee->termination_date)
            <div class="emp-banner emp-banner-terminated no-print">
                <i class="fas fa-calendar-xmark"></i>
                <span>Terminated {{ $employee->termination_date->format('d M Y') }} — removed from payroll runs after this date.</span>
            </div>
        @elseif ($onPayroll)
            <div class="emp-banner emp-banner-active no-print">
                <i class="fas fa-circle-check"></i>
                <span>Active on payroll — included in monthly payroll processing.</span>
            </div>
        @elseif (! $employee->is_active)
            <div class="emp-banner emp-banner-inactive no-print">
                <i class="fas fa-user-slash"></i>
                <span>Inactive employee record — not included in payroll.</span>
            </div>
        @endif

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Gross Salary</p>
                    <p class="mi-kpi-value emp-amt">{{ $salary ? number_format($salary->grossPay(), 2) : '—' }}</p>
                    <p class="emp-kpi-sub">KES per month</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Station</p>
                    <p class="mi-kpi-value text-base">{{ $employee->stationLabel() }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas {{ $employee->stationIcon() }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Payroll Status</p>
                    <p class="mi-kpi-value text-base">{{ $onPayroll ? 'On Payroll' : 'Off Payroll' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-money-check-dollar"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">System Access</p>
                    <p class="mi-kpi-value text-base">{{ $employee->hasSystemAccess() ? 'Yes' : 'No' }}</p>
                    @if ($employee->user?->roles->first())
                        <p class="emp-kpi-sub">{{ $employee->user->roles->first()->name }}</p>
                    @endif
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-key"></i></div>
            </div>
        </div>

        <div class="emp-show-grid">
            <div class="space-y-5">
                <div class="emp-doc-card">
                    <div class="emp-doc-head">
                        <div>
                            <h2><i class="fas fa-briefcase text-gray-400 text-sm mr-1"></i> Employment</h2>
                            <p>Role, station assignment, and dates</p>
                        </div>
                    </div>
                    <div class="emp-doc-body">
                        <dl class="emp-meta-grid">
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-briefcase"></i> Job Title</dt>
                                <dd>{{ $employee->job_title }}</dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-file-contract"></i> Employment Type</dt>
                                <dd>
                                    <span class="emp-type-pill {{ $employee->employmentPillClass() }}">{{ $employee->employmentTypeLabel() }}</span>
                                </dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt><i class="fas {{ $employee->stationIcon() }}"></i> Station</dt>
                                <dd>
                                    <span class="emp-station-pill {{ $employee->stationPillClass() }}">
                                        <i class="fas {{ $employee->stationIcon() }}"></i>
                                        {{ $employee->stationLabel() }}
                                    </span>
                                </dd>
                            </div>
                            @if ($employee->shop)
                                <div class="emp-meta-item">
                                    <dt><i class="fas fa-store"></i> Shop</dt>
                                    <dd>{{ $employee->shop->name }}</dd>
                                </div>
                            @endif
                            @if ($employee->warehouse)
                                <div class="emp-meta-item">
                                    <dt><i class="fas fa-warehouse"></i> Warehouse</dt>
                                    <dd>{{ $employee->warehouse->name }}</dd>
                                </div>
                            @endif
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-calendar"></i> Hire Date</dt>
                                <dd>{{ $employee->hire_date?->format('d M Y') ?? '—' }}</dd>
                            </div>
                            @if ($employee->termination_date)
                                <div class="emp-meta-item">
                                    <dt><i class="fas fa-calendar-xmark"></i> Termination</dt>
                                    <dd>{{ $employee->termination_date->format('d M Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="emp-doc-card">
                    <div class="emp-doc-head">
                        <div>
                            <h2><i class="fas fa-address-card text-gray-400 text-sm mr-1"></i> Contact &amp; IDs</h2>
                            <p>Personal and statutory identifiers</p>
                        </div>
                    </div>
                    <div class="emp-doc-body">
                        <dl class="emp-meta-grid">
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-phone"></i> Phone</dt>
                                <dd>{{ $employee->phone ?: '—' }}</dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-envelope"></i> Work Email</dt>
                                <dd>{{ $employee->email ?: '—' }}</dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-id-card"></i> National ID</dt>
                                <dd>{{ $employee->national_id ?: '—' }}</dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt><i class="fas fa-file-invoice"></i> KRA PIN</dt>
                                <dd class="emp-mono">{{ $employee->kra_pin ?: '—' }}</dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt>NSSF</dt>
                                <dd class="emp-mono">{{ $employee->nssf_number ?: '—' }}</dd>
                            </div>
                            <div class="emp-meta-item">
                                <dt>SHIF</dt>
                                <dd class="emp-mono">{{ $employee->shif_number ?: '—' }}</dd>
                            </div>
                            @if ($employee->address)
                                <div class="emp-meta-item full">
                                    <dt><i class="fas fa-location-dot"></i> Address</dt>
                                    <dd>{{ $employee->address }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="emp-doc-card">
                    <div class="emp-doc-head">
                        <div>
                            <h2><i class="fas fa-money-bill-wave text-gray-400 text-sm mr-1"></i> Current Salary</h2>
                            <p>Monthly compensation package</p>
                        </div>
                        @if ($salary?->effective_from)
                            <span class="text-xs text-gray-400">Since {{ $salary->effective_from->format('d M Y') }}</span>
                        @endif
                    </div>
                    @if ($salary)
                        <div class="emp-doc-body">
                            <div class="emp-salary-row"><span>Basic salary</span><span class="emp-amt">{{ number_format($salary->basic_salary, 2) }}</span></div>
                            <div class="emp-salary-row"><span>Housing allowance</span><span>{{ number_format($salary->housing_allowance, 2) }}</span></div>
                            <div class="emp-salary-row"><span>Transport allowance</span><span>{{ number_format($salary->transport_allowance, 2) }}</span></div>
                            <div class="emp-salary-row"><span>Other allowance</span><span>{{ number_format($salary->other_allowance, 2) }}</span></div>
                            <div class="emp-salary-row"><span>Payment method</span><span class="capitalize">{{ str_replace('_', ' ', $salary->payment_method) }}</span></div>
                            @if ($salary->bank_name)
                                <div class="emp-salary-row"><span>Bank</span><span>{{ $salary->bank_name }}</span></div>
                            @endif
                            @if ($salary->account_number)
                                <div class="emp-salary-row"><span>Account</span><span class="emp-mono">{{ $salary->account_number }}</span></div>
                            @endif
                        </div>
                        <div class="emp-doc-foot">
                            <span>Monthly gross</span>
                            <span class="emp-amt text-orange-700">KES {{ number_format($salary->grossPay(), 2) }}</span>
                        </div>
                    @else
                        <div class="emp-doc-body">
                            <p class="text-sm text-gray-400">No salary record on file.</p>
                        </div>
                    @endif
                </div>

                @if ($employee->salaries->count() > 1)
                    <div class="emp-doc-card">
                        <div class="emp-doc-head">
                            <div>
                                <h2><i class="fas fa-clock-rotate-left text-gray-400 text-sm mr-1"></i> Salary History</h2>
                                <p>{{ $employee->salaries->count() }} records</p>
                            </div>
                        </div>
                        <div class="emp-doc-body">
                            @foreach ($employee->salaries as $record)
                                <div class="emp-history-row">
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            KES {{ number_format($record->grossPay(), 2) }}
                                            @if ($record->id === $salary?->id)
                                                <span class="emp-badge emp-badge-green ml-1">Current</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ $record->effective_from?->format('d M Y') }}
                                            @if ($record->effective_to)
                                                – {{ $record->effective_to->format('d M Y') }}
                                            @else
                                                – present
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="mi-guide no-print">
                <div class="mi-guide-head">
                    <div class="mi-guide-icon"><i class="fas fa-bolt"></i></div>
                    <div>
                        <h2 class="mi-guide-title">Quick Actions</h2>
                        <p class="mi-guide-subtitle">Employee management</p>
                    </div>
                </div>
                <div class="mi-guide-body space-y-3">
                    @can('employees.manage')
                        <a href="{{ route('employees.edit', $employee) }}" class="mi-btn-orange w-full justify-center">
                            <i class="fas fa-pen text-xs"></i> Edit Employee
                        </a>
                    @endcan
                    @can('payroll.view')
                        <a href="{{ route('payroll.index') }}" class="mi-btn-ghost w-full justify-center">
                            <i class="fas fa-money-check-dollar text-xs"></i> Go to Payroll
                        </a>
                    @endcan

                    @if ($employee->user)
                        <div class="border-t border-gray-100 pt-3 mt-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">System Login</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $employee->user->email }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $employee->user->roles->pluck('name')->join(', ') ?: 'No role' }}</p>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Employment</p>
                        <ul class="text-sm text-gray-600 space-y-1.5">
                            <li><strong class="text-gray-800">Type:</strong> {{ $employee->employmentTypeLabel() }}</li>
                            <li><strong class="text-gray-800">Hired:</strong> {{ $employee->hire_date?->format('d M Y') ?? '—' }}</li>
                            @if ($employee->termination_date)
                                <li><strong class="text-gray-800">Terminated:</strong> {{ $employee->termination_date->format('d M Y') }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
