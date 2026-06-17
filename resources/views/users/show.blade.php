<x-app-layout title="User Details">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
            @can('users.edit')
                <a href="{{ route('users.edit', $user) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Edit</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $user->email }}</dd></div>
                    <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $user->phone ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Role</dt><dd class="text-gray-900">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="text-gray-900">{{ $user->is_active ? 'Active' : 'Inactive' }}</dd></div>
                    <div><dt class="text-gray-500">Shop</dt><dd class="text-gray-900">{{ $user->shop?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Warehouse</dt><dd class="text-gray-900">{{ $user->warehouse?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Last login</dt><dd class="text-gray-900">{{ $user->last_login_at?->format('d M Y H:i') ?? 'Never' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Recent Login History</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-3 py-2">Logged in</th>
                            <th class="px-3 py-2">Logged out</th>
                            <th class="px-3 py-2">IP</th>
                            <th class="px-3 py-2">Device</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($user->logins as $login)
                            <tr>
                                <td class="px-3 py-2">{{ $login->logged_in_at?->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2">{{ $login->logged_out_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $login->ip_address ?? '—' }}</td>
                                <td class="px-3 py-2 text-gray-500 truncate max-w-xs">{{ $login->user_agent ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">No login history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
