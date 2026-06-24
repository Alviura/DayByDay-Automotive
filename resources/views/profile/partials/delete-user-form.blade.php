<section class="mi-form-body">
    <div class="mi-card-head prf-danger-head">
        <div class="flex items-center gap-2 text-rose-800">
            <i class="fas fa-triangle-exclamation text-rose-500 text-sm"></i>
            <span class="text-sm font-semibold">Delete Account</span>
        </div>
    </div>

    <div class="p-4 border-t border-rose-100">
        <p class="text-sm text-gray-600 leading-relaxed">
            Permanently remove your account and all associated data. This action cannot be undone.
        </p>

        <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            <i class="fas fa-trash-can text-xs"></i> Delete Account
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-gray-900">Delete your account?</h2>
            <p class="mt-2 text-sm text-gray-600">
                Enter your password to confirm. All of your data will be permanently removed.
            </p>

            <div class="mt-5">
                <label class="mi-field-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="mi-input block w-full"
                       placeholder="Your current password" autocomplete="current-password">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-2">
                <button type="button" class="mi-btn-ghost" x-on:click="$dispatch('close')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                    <i class="fas fa-trash-can text-xs"></i> Delete Account
                </button>
            </div>
        </form>
    </x-modal>
</section>
