<x-app-layout :title="'Edit '.$series->displayName()">
    @push('styles')
        <x-module.page-index-styles />
        @include('quotation-series.partials.show-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ tab: '{{ request('tab', 'details') }}' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Edit Quotation Series</h1>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $series->displayName() }}</p>
                </div>
            </div>
            <a href="{{ route('quotation-series.show', $series) }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Series
            </a>
        </div>

        <div class="mi-tab-bar">
            <button type="button" @click="tab = 'details'" :class="{ 'active': tab === 'details' }">
                <i class="fas fa-circle-info"></i> Series Details
            </button>
            @if ($series->canManageQuotationItems())
                <button type="button" @click="tab = 'quotation'" :class="{ 'active': tab === 'quotation' }">
                    <i class="fas fa-list"></i> Quotation Lines
                    <span class="mi-cat-badge !text-[0.62rem] !py-0">{{ $series->items->count() }}</span>
                </button>
            @endif
        </div>

        <div x-show="tab === 'details'" x-transition>
            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-pen-to-square text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Overview &amp; Rates</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('quotation-series.update', $series) }}" class="mi-form-body space-y-4">
                    @csrf
                    @method('PUT')
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
                    <div class="mi-form-actions border-t border-gray-100 pt-4">
                        <a href="{{ route('quotation-series.show', $series) }}" class="mi-btn-ghost">Cancel</a>
                        <button type="submit" class="mi-btn-orange">Save Details</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($series->canManageQuotationItems())
            <div x-show="tab === 'quotation'" x-cloak x-transition>
                @if ($series->status === 'order_draft')
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 mb-4">
                        <i class="fas fa-circle-info text-amber-600 mr-1"></i>
                        Removing or changing lines clears calculated margins — re-save prices and <strong>Calculate Margins</strong> on the Order tab.
                    </div>
                @endif
                @include('quotation-series.partials.quotation-tab', ['editContext' => true])
            </div>
        @endif
    </div>
</x-app-layout>
