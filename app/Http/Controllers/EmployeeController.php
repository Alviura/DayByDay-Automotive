<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {
        $this->middleware('permission:employees.view')->only(['index', 'show']);
        $this->middleware('permission:employees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->status ?: 'all';

        $employees = Employee::with(['shop', 'warehouse', 'user.roles', 'currentSalary'])
            ->search($request->search)
            ->when($statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($statusFilter === 'payroll', fn ($q) => $q->onPayroll())
            ->when($request->station, fn ($q) => $q->where('station_type', $request->station))
            ->when($request->employment_type, fn ($q) => $q->where('employment_type', $request->employment_type))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('first_name')->orderBy('last_name'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['name', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $onPayroll = Employee::onPayroll()->with('currentSalary')->get();
        $monthlyGross = round($onPayroll->sum(fn (Employee $e) => (float) ($e->currentSalary?->grossPay() ?? 0)), 2);

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('is_active', true)->count(),
            'inactive' => Employee::where('is_active', false)->count(),
            'on_payroll' => Employee::onPayroll()->count(),
            'with_login' => Employee::whereNotNull('user_id')->count(),
            'monthly_gross' => $monthlyGross,
        ];

        $pipeline = [
            ['key' => 'all', 'label' => 'All Staff', 'count' => Employee::count(), 'icon' => 'fa-users'],
            ['key' => 'active', 'label' => 'Active', 'count' => $stats['active'], 'icon' => 'fa-circle-check'],
            ['key' => 'payroll', 'label' => 'On Payroll', 'count' => $stats['on_payroll'], 'icon' => 'fa-money-check-dollar'],
            ['key' => 'inactive', 'label' => 'Inactive', 'count' => $stats['inactive'], 'icon' => 'fa-user-slash'],
        ];

        return view('employees.index', compact('employees', 'stats', 'pipeline', 'statusFilter'));
    }

    public function create(): View
    {
        return view('employees.create', $this->formData());
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->employeeService->create(
            $this->employeeAttributes($request),
            $this->salaryAttributes($request),
            $this->userAttributes($request)
        );

        return redirect()->route('employees.index')->with('status', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        $employee->load(['shop', 'warehouse', 'user.roles', 'currentSalary', 'salaries' => fn ($q) => $q->latest('effective_from')]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['currentSalary', 'user.roles']);

        return view('employees.edit', array_merge(['employee' => $employee], $this->formData()));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update(
            $employee,
            $this->employeeAttributes($request),
            $this->salaryAttributes($request),
            $this->userAttributes($request, $employee)
        );

        return redirect()->route('employees.show', $employee)->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('status', 'Employee archived successfully.');
    }

    private function formData(): array
    {
        return [
            'shops' => Shop::active()->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'roles' => Role::whereIn('name', ['Shop Manager', 'Shop Attendant', 'Warehouse Manager', 'Administrator'])->orderBy('name')->get(),
        ];
    }

    private function employeeAttributes(StoreEmployeeRequest|UpdateEmployeeRequest $request): array
    {
        return [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'national_id' => $request->national_id,
            'kra_pin' => $request->kra_pin,
            'nssf_number' => $request->nssf_number,
            'shif_number' => $request->shif_number,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'job_title' => $request->job_title,
            'employment_type' => $request->employment_type,
            'hire_date' => $request->hire_date,
            'termination_date' => $request->termination_date,
            'station_type' => $request->station_type,
            'shop_id' => $request->shop_id,
            'warehouse_id' => $request->warehouse_id,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function salaryAttributes(StoreEmployeeRequest|UpdateEmployeeRequest $request): array
    {
        return [
            'basic_salary' => $request->basic_salary,
            'housing_allowance' => $request->housing_allowance ?? 0,
            'transport_allowance' => $request->transport_allowance ?? 0,
            'other_allowance' => $request->other_allowance ?? 0,
            'payment_method' => $request->payment_method,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'effective_from' => $request->effective_from,
        ];
    }

    private function userAttributes(StoreEmployeeRequest|UpdateEmployeeRequest $request, ?Employee $employee = null): ?array
    {
        if ($employee?->user_id) {
            $data = [];
            if ($request->filled('user_role')) {
                $data['role'] = $request->user_role;
            }
            if ($request->filled('user_password')) {
                $data['password'] = $request->user_password;
            }

            return $data ?: null;
        }

        if (! $request->boolean('create_user')) {
            return null;
        }

        return [
            'email' => $request->user_email,
            'password' => $request->user_password,
            'role' => $request->user_role,
        ];
    }
}
