<x-app-layout title="Edit Role">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Role') }}: {{ $role->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    @include('roles._form')

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('roles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
