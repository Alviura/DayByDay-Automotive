<section class="mi-form-body">
    <div class="mi-card-head">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fas fa-user-pen text-gray-400 text-sm"></i>
            <span class="text-sm font-semibold">Profile Information</span>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="p-4 space-y-5 border-t border-gray-100">
        @csrf
        @method('patch')

        <div>
            <label class="mi-field-label" for="name"><i class="fas fa-user"></i> Full name</label>
            <input id="name" name="name" type="text" class="mi-input block w-full"
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label class="mi-field-label" for="email"><i class="fas fa-envelope"></i> Email address</label>
            <input id="email" name="email" type="email" class="mi-input block w-full"
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-900">
                    <p>Your email address is unverified.</p>
                    <button form="send-verification" type="submit"
                            class="mt-1 text-sm font-semibold text-amber-800 underline hover:text-amber-950">
                        Resend verification email
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-700">A new verification link has been sent.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="mi-form-actions" style="border-top:none;padding-top:0">
            <button type="submit" class="mi-btn-orange">
                <i class="fas fa-check text-xs"></i> Save Profile
            </button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-green-600">Saved successfully.</p>
            @endif
        </div>
    </form>
</section>
