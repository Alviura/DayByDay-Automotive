<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /** @var list<string> */
    public const CORE_ROLES = [
        'Administrator',
        'Shop Manager',
        'Warehouse Manager',
        'Shop Attendant',
    ];

    public function __construct()
    {
        $this->middleware('permission:roles.view')->only(['index']);
        $this->middleware('permission:roles.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(): View
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        $stats = [
            'total_roles' => $roles->count(),
            'total_permissions' => count(PermissionRegistry::allNames()),
            'assigned_users' => User::count(),
            'custom_roles' => $roles->whereNotIn('name', self::CORE_ROLES)->count(),
        ];

        return view('roles.index', [
            'roles' => $roles,
            'stats' => $stats,
            'coreRoles' => self::CORE_ROLES,
        ]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $role->loadCount(['permissions', 'users']);

        return view('roles.edit', [
            'role' => $role,
            'permissions' => $this->groupedPermissions(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::CORE_ROLES, true)) {
            return back()->with('error', 'Core system roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted successfully.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{key: string, label: string, permissions: \Illuminate\Support\Collection}>
     */
    private function groupedPermissions()
    {
        return PermissionRegistry::groupedWithMeta();
    }
}
