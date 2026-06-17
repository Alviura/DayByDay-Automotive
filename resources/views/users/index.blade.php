<x-app-layout title="Users">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Users') }}</h2>
            @can('users.create')
                <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('New User') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 flex gap-2">
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="w-full sm:w-80" />
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Role</th>
                                <th class="px-3 py-2">Location</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-3 py-2">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $user->shop?->name ?? $user->warehouse?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        @if ($user->is_active)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right space-x-3 whitespace-nowrap">
                                        @can('users.view')
                                            <a href="{{ route('users.show', $user) }}" class="text-gray-600 hover:text-gray-900">View</a>
                                        @endcan
                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        @endcan
                                        @can('users.delete')
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Archive this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Archive</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-gray-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
