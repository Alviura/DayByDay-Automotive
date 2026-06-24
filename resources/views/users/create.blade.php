<x-app-layout title="New User">

    @push('styles')
        <x-module.page-index-styles />
        @include('users.partials.page-styles')
    @endpush

    <div class="mi-page usr-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-user-plus"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">New User</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Create a team account with role and location assignment.</p>
                </div>
            </div>
            <a href="{{ route('users.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Users
            </a>
        </div>

        <div class="mi-form-split">
            <div class="mi-card mi-form-main">
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-pen-to-square text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Account details</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mi-form-body">
                        @include('users._form')
                    </div>
                    <div class="mi-form-actions">
                        <a href="{{ route('users.index') }}" class="mi-btn-ghost">Cancel</a>
                        <button type="submit" class="mi-btn-orange">
                            <i class="fas fa-plus text-xs"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
            @include('users.partials.form-guide')
        </div>
    </div>
</x-app-layout>
