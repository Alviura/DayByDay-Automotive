<x-app-layout title="Roles & Permissions">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Roles & Permissions') }}</h2>
            @can('roles.manage')
                <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('New Role') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-3 py-2">Role</th>
                            <th class="px-3 py-2">Permissions</th>
                            <th class="px-3 py-2">Users</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($roles as $role)
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $role->name }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $role->permissions_count }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $role->users_count }}</td>
                                <td class="px-3 py-2 text-right space-x-3 whitespace-nowrap">
                                    @can('roles.manage')
                                        <a href="{{ route('roles.edit', $role) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        @unless (in_array($role->name, ['Administrator', 'Shop Manager'], true))
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline" data-confirm="Delete this role?" data-confirm-variant="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">No roles defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
