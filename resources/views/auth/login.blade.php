<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative mt-1.5">
                <i class="fas fa-envelope pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <x-text-input id="email" class="block w-full ps-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1.5">
                <i class="fas fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <x-text-input id="password" class="block w-full ps-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password"
                                placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-blue-600 transition hover:text-blue-700 hover:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Submit -->
        <x-primary-button class="w-full justify-center py-3 text-sm">
            <i class="fas fa-arrow-right-to-bracket me-2"></i> {{ __('Sign in') }}
        </x-primary-button>
    </form>
</x-guest-layout>
