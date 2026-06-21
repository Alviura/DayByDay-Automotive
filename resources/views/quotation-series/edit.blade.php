<x-app-layout :title="'Edit '.$series->displayName()">
    @push('styles')<x-module.page-index-styles />@endpush
    <x-module.form-page
        title="Edit Quotation Series"
        :subtitle="$series->displayName()"
        icon="fa-folder-open"
        card-title="Series Details"
        :back-url="route('quotation-series.show', $series)"
        :action="route('quotation-series.update', $series)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <div class="mi-form-grid">
            <div>
                <label class="mi-field-label">Supplier</label>
                <select name="supplier_id" class="mi-select" required>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $series->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mi-span-full">
                <label class="mi-field-label">Description</label>
                <x-text-input name="description" class="mi-input block w-full" :value="old('description', $series->description)" />
            </div>
            <div>
                <label class="mi-field-label">Purchase Type</label>
                <select name="purchase_type" class="mi-select" required>
                    <option value="local" @selected(old('purchase_type', $series->purchase_type) === 'local')>Local</option>
                    <option value="import" @selected(old('purchase_type', $series->purchase_type) === 'import')>Import</option>
                </select>
            </div>
            <div>
                <label class="mi-field-label">Currency</label>
                <select name="currency" class="mi-select" required>
                    @foreach (['KES', 'USD', 'EUR', 'GBP', 'JPY', 'CNY'] as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', $series->currency) === $currency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mi-field-label">Conversion (R)</label>
                <x-text-input name="exchange_rate" type="number" step="0.000001" class="mi-input block w-full" :value="old('exchange_rate', $series->exchange_rate)" />
            </div>
            <div>
                <label class="mi-field-label">CBM (R)</label>
                <x-text-input name="cbm_rate" type="number" step="0.01" class="mi-input block w-full" :value="old('cbm_rate', $series->cbm_rate)" />
            </div>
            <div class="mi-span-full">
                <label class="mi-field-label">Notes</label>
                <textarea name="notes" rows="2" class="mi-input block w-full">{{ old('notes', $series->notes) }}</textarea>
            </div>
        </div>
    </x-module.form-page>
</x-app-layout>
