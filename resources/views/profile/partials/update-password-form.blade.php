<section class="mi-form-body">
    <div class="mi-card-head">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fas fa-key text-gray-400 text-sm"></i>
            <span class="text-sm font-semibold">Update Password</span>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="p-4 space-y-5 border-t border-gray-100">
        @csrf
        @method('put')

        <div>
            <label class="mi-field-label" for="update_password_current_password">Current password</label>
            <input id="update_password_current_password" name="current_password" type="password"
                   class="mi-input block w-full" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div class="mi-form-grid">
            <div>
                <label class="mi-field-label" for="update_password_password">New password</label>
                <input id="update_password_password" name="password" type="password"
                       class="mi-input block w-full" autocomplete="new-password">
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
            </div>
            <div>
                <label class="mi-field-label" for="update_password_password_confirmation">Confirm password</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                       class="mi-input block w-full" autocomplete="new-password">
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
            </div>
        </div>

        <div class="mi-form-actions" style="border-top:none;padding-top:0">
            <button type="submit" class="mi-btn-orange">
                <i class="fas fa-lock text-xs"></i> Update Password
            </button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-green-600">Password updated.</p>
            @endif
        </div>
    </form>
</section>
