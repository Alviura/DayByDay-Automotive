<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Shop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $statusFilter = (string) $request->get('status', '');

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All Users', 'icon' => 'fa-users', 'count' => $stats['total']],
            ['key' => 'active', 'label' => 'Active', 'icon' => 'fa-circle-check', 'count' => $stats['active']],
            ['key' => 'inactive', 'label' => 'Inactive', 'icon' => 'fa-ban', 'count' => $stats['inactive']],
            [
                'key' => 'unassigned',
                'label' => 'No Location',
                'icon' => 'fa-location-dot',
                'count' => User::whereNull('shop_id')->whereNull('warehouse_id')->count(),
            ],
        ];

        $users = User::with(['roles', 'shop', 'warehouse'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when(
                $statusFilter === 'unassigned',
                fn ($q) => $q->whereNull('shop_id')->whereNull('warehouse_id')
            )
            ->when($request->role, fn ($q, $role) => $q->role($role))
            ->when(
                $request->sort === 'name',
                fn ($q) => $q->orderBy('name'),
                fn ($q) => $q->when(
                    $request->sort === 'oldest',
                    fn ($q) => $q->oldest(),
                    fn ($q) => $q->latest()
                )
            )
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'stats' => $stats,
            'pipeline' => $pipeline,
            'statusFilter' => $statusFilter,
            'roleOptions' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => Role::orderBy('name')->get(),
            'shops' => Shop::active()->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'shop_id' => $data['shop_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'shop', 'warehouse', 'logins' => fn ($q) => $q->latest('logged_in_at')->limit(20)]);

        return view('users.show', [
            'user' => $user,
            'loginCount' => $user->logins()->count(),
            'recentLoginCount' => $user->logins()->where('logged_in_at', '>=', now()->subDays(30))->count(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
            'shops' => Shop::active()->orderBy('name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'shop_id' => $data['shop_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User archived successfully.');
    }
}
