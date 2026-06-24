<x-app-layout :title="'Edit — '.$user->name">

    @push('styles')
        <x-module.page-index-styles />
        @include('users.partials.page-styles')
    @endpush

    <div class="mi-page usr-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-user-pen"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Edit User</h1>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('users.show', $user) }}" class="mi-btn-ghost"><i class="fas fa-eye text-xs"></i> View</a>
                <a href="{{ route('users.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="mi-card mi-form-main">
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-pen-to-square text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Update account</span>
                    </div>
                    @if ($user->roleName())
                        <span class="usr-role-pill {{ $user->rolePillClass() }}">{{ $user->roleName() }}</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    <div class="mi-form-body">
                        @include('users._form')
                    </div>
                    <div class="mi-form-actions">
                        <a href="{{ route('users.index') }}" class="mi-btn-ghost">Cancel</a>
                        <button type="submit" class="mi-btn-orange">
                            <i class="fas fa-check text-xs"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
            @include('users.partials.form-guide')
        </div>
    </div>
</x-app-layout>
