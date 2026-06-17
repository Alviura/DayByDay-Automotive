@props(['vehicleModel' => null, 'makes' => [], 'selectedMakeId' => null])

<div class="mi-form-grid">
    <div>
        <label for="vehicle_make_id" class="mi-field-label">
            <i class="fas fa-car-side"></i> Vehicle Make
        </label>
        <select id="vehicle_make_id" name="vehicle_make_id" class="mi-select" required>
            <option value="">Select make…</option>
            @foreach ($makes as $make)
                <option value="{{ $make->id }}"
                    @selected(old('vehicle_make_id', $vehicleModel->vehicle_make_id ?? $selectedMakeId) == $make->id)>
                    {{ $make->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('vehicle_make_id')" class="mt-1.5" />
    </div>

    <div>
        <label for="name" class="mi-field-label">
            <i class="fas fa-car"></i> Model Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $vehicleModel->name ?? '')" required autofocus placeholder="e.g. Corolla" />
        <p class="mi-field-hint">Must be unique within the selected make.</p>
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this model is available for product fitment and filters.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $vehicleModel->is_active ?? true))>
    </label>
</div>
