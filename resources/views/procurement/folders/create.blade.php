<x-app-layout title="New Procurement Folder">
    @push('styles')<x-module.page-index-styles />@endpush
    <x-module.form-page
        title="New Procurement Folder"
        subtitle="Start a procurement quotation folder for a supplier."
        icon="fa-folder-open"
        card-title="Folder Details"
        :back-url="route('procurement.folders.index')"
        :action="route('procurement.folders.store')"
        submit-label="Create Folder"
    >
        <div class="mi-form-grid">
            <div>
                <label class="mi-field-label">Supplier</label>
                <select name="supplier_id" class="mi-select" required>
                    <option value="">Select supplier…</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('supplier_id')" class="mt-1.5" />
            </div>
            <div>
                <label class="mi-field-label">Currency</label>
                <select name="currency" class="mi-select" required>
                    @foreach (['KES', 'USD', 'EUR', 'GBP', 'JPY', 'CNY'] as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', 'KES') === $currency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mi-field-label">Exchange Rate</label>
                <x-text-input name="exchange_rate" type="number" step="0.000001" min="0" class="mi-input block w-full" :value="old('exchange_rate', '1')" />
            </div>
            <div>
                <label class="mi-field-label">Import Type</label>
                <x-text-input name="import_type" class="mi-input block w-full" :value="old('import_type')" placeholder="e.g. Sea freight, Local" />
            </div>
            <div>
                <label class="mi-field-label">Est. Freight</label>
                <x-text-input name="total_freight" type="number" step="0.01" min="0" class="mi-input block w-full" :value="old('total_freight', '0')" />
            </div>
            <div>
                <label class="mi-field-label">Est. Tax</label>
                <x-text-input name="total_tax" type="number" step="0.01" min="0" class="mi-input block w-full" :value="old('total_tax', '0')" />
            </div>
            <div class="mi-span-full">
                <label class="mi-field-label">Notes</label>
                <textarea name="notes" rows="2" class="mi-input block w-full">{{ old('notes') }}</textarea>
            </div>
        </div>
    </x-module.form-page>
</x-app-layout>
