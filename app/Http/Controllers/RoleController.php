<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.view')->only(['index']);
        $this->middleware('permission:roles.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(): View
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('roles.index', compact('roles'));
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
        if (in_array($role->name, ['Administrator', 'Shop Manager'], true)) {
            return back()->with('error', 'Core system roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted successfully.');
    }

    /**
     * Group permissions by their module prefix for display.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection>
     */
    private function groupedPermissions()
    {
        return Permission::orderBy('name')->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
    }
}
