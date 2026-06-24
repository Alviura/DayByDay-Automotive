<x-guest-layout heading="Welcome back" subheading="Sign in to manage parts, sales, and stock across your workshops.">

    @if (session('status'))
        <p class="auth-status mb-4">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <label for="email" class="auth-label">Email address</label>
            <div class="relative">
                <i class="fas fa-envelope pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400"></i>
                <input id="email" class="auth-input" type="email" name="email"
                       value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="you@company.com">
            </div>
            @error('email')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="auth-label">Password</label>
            <div class="relative">
                <i class="fas fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400"></i>
                <input id="password"
                       class="auth-input auth-input--password"
                       :type="showPassword ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="current-password"
                       aria-describedby="password-hint">
                <button type="button"
                        class="auth-toggle-pw"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        :title="showPassword ? 'Hide password' : 'Show password'">
                    <i class="fas text-sm" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <p id="password-hint" class="mt-1.5 text-xs text-zinc-400">Enter the password provided by your administrator.</p>
            @error('password')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-3 pt-0.5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox"
                       class="rounded border-zinc-300 text-orange-600 shadow-sm focus:ring-orange-500"
                       name="remember">
                <span class="ms-2 auth-check-label">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link text-sm shrink-0" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="auth-submit">
            <i class="fas fa-arrow-right-to-bracket"></i> Sign in to workspace
        </button>
    </form>
</x-guest-layout>
