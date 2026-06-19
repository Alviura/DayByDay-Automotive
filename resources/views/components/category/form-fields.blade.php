@props(['category' => null, 'parentOptions' => [], 'selectedParentId' => null])

<div class="mi-form-grid">
    <div>
        <label for="name" class="mi-field-label">
            <i class="fas fa-tag"></i> Category Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $category->name ?? '')" required autofocus placeholder="e.g. Engine Parts" />
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <label for="parent_id" class="mi-field-label">
            <i class="fas fa-sitemap"></i> Parent Category
        </label>
        <select id="parent_id" name="parent_id" class="mi-select">
            <option value="">None — top level</option>
            @foreach ($parentOptions as $option)
                <option value="{{ $option->id }}"
                    @selected(old('parent_id', $category->parent_id ?? $selectedParentId) == $option->id)>
                    {{ $option->name }}@if ($option->parent) (under {{ $option->parent->name }})@endif
                </option>
            @endforeach
        </select>
        <p class="mi-field-hint">Leave empty for a top-level category.</p>
        <x-input-error :messages="$errors->get('parent_id')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this category appears when classifying products.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $category->is_active ?? true))>
    </label>
</div>
