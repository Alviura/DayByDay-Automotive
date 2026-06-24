@php $active = $active ?? 'index'; @endphp
<nav class="emp-nav no-print" aria-label="HR modules">
    <a href="{{ route('employees.index') }}" class="emp-nav-link {{ $active === 'index' ? 'active' : '' }}">
        <i class="fas fa-users"></i> Employees
    </a>
    @can('employees.manage')
        <a href="{{ route('employees.create') }}" class="emp-nav-link {{ $active === 'create' ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i> Add Employee
        </a>
    @endcan
    @can('payroll.view')
        <a href="{{ route('payroll.index') }}" class="emp-nav-link ml-auto">
            <i class="fas fa-money-check-dollar"></i> Payroll
        </a>
    @endcan
</nav>
