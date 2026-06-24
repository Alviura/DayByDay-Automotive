@php
    $accountTypes = \App\Enums\AccountType::cases();
    $isEdit = isset($chartOfAccount);
    $account = $chartOfAccount ?? null;
@endphp
<x-app-layout :title="$isEdit ? 'Edit Account' : 'New Account'">

    @push('styles')<x-module.page-index-styles />@endpush

    <div class="mi-page max-w-2xl space-y-5">
        <h1 class="text-xl font-bold">{{ $isEdit ? 'Edit Account' : 'New Account' }}</h1>

        <form method="POST" action="{{ $isEdit ? route('chart-of-accounts.update', $account) : route('chart-of-accounts.store') }}" class="mi-form-card space-y-4">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mi-field-label">Code</label>
                    <input type="text" name="code" class="mi-input w-full" value="{{ old('code', $account?->code) }}" required @disabled($account?->is_system)>
                    @error('code')<p class="mi-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mi-field-label">Type</label>
                    <select name="account_type" class="mi-select w-full" required>
                        @foreach ($accountTypes as $type)
                            <option value="{{ $type->value }}" @selected(old('account_type', $account?->account_type?->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="mi-field-label">Name</label>
                <input type="text" name="name" class="mi-input w-full" value="{{ old('name', $account?->name) }}" required>
            </div>

            <div>
                <label class="mi-field-label">Description</label>
                <textarea name="description" class="mi-input w-full" rows="2">{{ old('description', $account?->description) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account?->is_active ?? true))>
                Active
            </label>

            <div class="flex gap-2">
                <button type="submit" class="mi-btn-orange">Save</button>
                <a href="{{ route('chart-of-accounts.index') }}" class="mi-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
