<x-app-layout :title="'Edit '.$folder->folder_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <x-module.form-page
        title="Edit Folder"
        :subtitle="$folder->folder_number"
        icon="fa-folder-open"
        card-title="Folder Details"
        :back-url="route('procurement.folders.show', $folder)"
        :action="route('procurement.folders.update', $folder)"
        method="PUT"
        submit-label="Save Changes"
        :is-edit="true"
    >
        <div class="mi-form-grid">
            <div>
                <label class="mi-field-label">Supplier</label>
                <select name="supplier_id" class="mi-select" required>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $folder->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mi-field-label">Currency</label>
                <select name="currency" class="mi-select" required>
                    @foreach (['KES', 'USD', 'EUR', 'GBP'] as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', $folder->currency) === $currency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="mi-field-label">Exchange Rate</label><x-text-input name="exchange_rate" type="number" step="0.000001" class="mi-input block w-full" :value="old('exchange_rate', $folder->exchange_rate)" /></div>
            <div><label class="mi-field-label">Import Type</label><x-text-input name="import_type" class="mi-input block w-full" :value="old('import_type', $folder->import_type)" /></div>
            <div><label class="mi-field-label">Est. Freight</label><x-text-input name="total_freight" type="number" step="0.01" class="mi-input block w-full" :value="old('total_freight', $folder->total_freight)" /></div>
            <div><label class="mi-field-label">Est. Tax</label><x-text-input name="total_tax" type="number" step="0.01" class="mi-input block w-full" :value="old('total_tax', $folder->total_tax)" /></div>
            <div class="mi-span-full"><label class="mi-field-label">Notes</label><textarea name="notes" rows="2" class="mi-input block w-full">{{ old('notes', $folder->notes) }}</textarea></div>
        </div>
    </x-module.form-page>
</x-app-layout>
