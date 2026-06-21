<x-app-layout title="New Quotation Series">
    @push('styles')
        <x-module.page-index-styles />
        <style>
            .qs-create-preview {
                border: 1px dashed #fed7aa;
                background: linear-gradient(135deg, #fffbeb, #fff7ed);
                border-radius: 10px;
                padding: .85rem 1rem;
            }
            .qs-create-preview-label {
                font-size: .62rem; font-weight: 700; letter-spacing: .06em;
                text-transform: uppercase; color: #9ca3af; margin-bottom: .35rem;
            }
            .qs-create-preview-title {
                font-size: .84rem; font-weight: 700; color: #9a3412; line-height: 1.45;
            }
            .qs-create-preview-ref {
                font-size: .72rem; color: #b45309; margin-top: .25rem;
            }
            .qs-import-panel {
                grid-column: 1 / -1;
                border: 1px solid #e0e7ff;
                background: #f8fafc;
                border-radius: 10px;
                padding: 1rem 1.15rem;
            }
            .qs-import-panel-title {
                font-size: .72rem; font-weight: 700; color: #4338ca;
                text-transform: uppercase; letter-spacing: .05em;
                display: flex; align-items: center; gap: .4rem; margin-bottom: .85rem;
            }
        </style>
    @endpush

    <x-module.form-page
        title="New Quotation Series"
        subtitle="Phase 1 — set up supplier, purchase type, and import rates before adding products."
        icon="fa-file-invoice-dollar"
        card-title="Series Details"
        :back-url="route('quotation-series.index')"
        :action="route('quotation-series.store')"
        submit-label="Create & Add Products"
    >
        <x-slot:cardMeta>
            <span class="mi-cat-badge">Phase 1 — Create</span>
        </x-slot:cardMeta>

        <div class="mi-form-grid" x-data="quotationSeriesForm()" x-init="init()">
            {{-- Live title preview --}}
            <div class="mi-span-full qs-create-preview" x-show="supplierName" x-cloak>
                <p class="qs-create-preview-label">Preview display title</p>
                <p class="qs-create-preview-title" x-text="previewTitle()"></p>
                <p class="qs-create-preview-ref">Reference will be assigned on save (PF-{{ date('Y') }}-####)</p>
            </div>

            <div class="mi-span-full">
                <label class="mi-field-label">Supplier <span class="text-rose-500">*</span></label>
                <select name="supplier_id" class="mi-select" required x-model="supplierId" @change="loadDefaults()">
                    <option value="">Select supplier…</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                                data-name="{{ $supplier->name }}"
                                data-currency="{{ $supplier->currency }}"
                                data-purchase-type="{{ $supplier->purchase_type ?? 'local' }}">
                            {{ $supplier->name }}
                            @if ($supplier->purchase_type)
                                ({{ ucfirst($supplier->purchase_type) }} · {{ $supplier->currency ?? 'KES' }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('supplier_id')" class="mt-1.5" />
            </div>

            <div class="mi-span-full">
                <label class="mi-field-label">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <x-text-input name="description" class="mi-input block w-full" x-model="description"
                              :value="old('description')" placeholder="e.g. TUBE SHARK / NISSAN AIR CLEANERS" />
                <p class="mt-1 text-xs text-gray-400">Appended after supplier name in the display title.</p>
                <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
            </div>

            <div>
                <label class="mi-field-label">Purchase Type</label>
                <select name="purchase_type" class="mi-select" x-model="purchaseType">
                    <option value="local">Local — KES supplier pricing</option>
                    <option value="import">Import — foreign currency + CBM</option>
                </select>
                <x-input-error :messages="$errors->get('purchase_type')" class="mt-1.5" />
            </div>

            <div>
                <label class="mi-field-label">Currency</label>
                <select name="currency" class="mi-select" x-model="currency">
                    @foreach (['KES', 'USD', 'EUR', 'GBP', 'JPY', 'CNY'] as $cur)
                        <option value="{{ $cur }}">{{ $cur }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('currency')" class="mt-1.5" />
            </div>

            <div class="qs-import-panel" x-show="purchaseType === 'import'" x-cloak>
                <p class="qs-import-panel-title"><i class="fas fa-ship"></i> Import rates</p>
                <div class="mi-form-grid">
                    <div>
                        <label class="mi-field-label">Conversion (R) <span class="text-rose-500">*</span></label>
                        <x-text-input name="exchange_rate" type="number" step="0.000001" min="0"
                                      class="mi-input block w-full" x-model="exchangeRate"
                                      placeholder="e.g. 31.5" />
                        <p class="mt-1 text-xs text-gray-400">Foreign currency → KES (required for import)</p>
                        <x-input-error :messages="$errors->get('exchange_rate')" class="mt-1.5" />
                    </div>
                    <div>
                        <label class="mi-field-label">CBM (R) <span class="text-rose-500">*</span></label>
                        <x-text-input name="cbm_rate" type="number" step="0.01" min="0"
                                      class="mi-input block w-full" :value="old('cbm_rate')"
                                      placeholder="e.g. 55033" />
                        <p class="mt-1 text-xs text-gray-400">Transport cost per m³ in KES</p>
                        <x-input-error :messages="$errors->get('cbm_rate')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <div class="mi-span-full">
                <label class="mi-field-label">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="notes" rows="3" class="mi-input block w-full" placeholder="Internal notes for your team…">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
            </div>
        </div>

        <x-slot:guide>
            <x-quotation-series.form-guide />
        </x-slot:guide>
    </x-module.form-page>

    @push('scripts')
    <script>
        function quotationSeriesForm() {
            return {
                supplierId: '{{ old('supplier_id', '') }}',
                supplierName: '',
                description: @json(old('description', '')),
                purchaseType: '{{ old('purchase_type', 'local') }}',
                currency: '{{ old('currency', 'KES') }}',
                exchangeRate: '{{ old('exchange_rate', '1') }}',
                init() {
                    if (this.supplierId) this.loadDefaults();
                },
                loadDefaults() {
                    const opt = document.querySelector(`select[name="supplier_id"] option[value="${this.supplierId}"]`);
                    if (!opt) {
                        this.supplierName = '';
                        return;
                    }
                    this.supplierName = opt.dataset.name || '';
                    this.currency = opt.dataset.currency || 'KES';
                    this.purchaseType = opt.dataset.purchaseType || 'local';
                    if (this.purchaseType === 'local') this.exchangeRate = '1';
                },
                previewTitle() {
                    if (!this.supplierName) return '';
                    const date = new Date();
                    const months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
                    const d = String(date.getDate()).padStart(2, '0');
                    const prefix = `${d}${months[date.getMonth()]}${date.getFullYear()} - ${this.supplierName}`;
                    return this.description?.trim() ? `${prefix} - ${this.description.trim()}` : prefix;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
