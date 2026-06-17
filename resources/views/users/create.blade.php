<x-app-layout title="New User">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New User') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    @include('users._form')

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Create User') }}</x-primary-button>
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
