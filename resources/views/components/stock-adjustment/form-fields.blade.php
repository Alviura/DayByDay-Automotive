@props(['warehouses' => collect(), 'shops' => collect(), 'products' => collect()])

<div
    x-data="{
        locationType: @js(old('location_type', 'warehouse')),
        locationId: @js(old('location_id', $warehouses->first()?->id ?? '')),
        warehouses: @js($warehouses->map(fn ($w) => ['id' => $w->id, 'label' => $w->name.' ('.$w->code.')'])->values()),
        shops: @js($shops->map(fn ($s) => ['id' => $s->id, 'label' => $s->name.' ('.$s->code.')'])->values()),
        rows: @js(old('items', [['product_id' => '', 'counted_quantity' => '', 'system_quantity' => '', 'loading' => false]])),
        init() {
            this.$watch('locationType', () => {
                const list = this.locationType === 'warehouse' ? this.warehouses : this.shops;
                this.locationId = list[0]?.id ?? '';
            });
            this.$watch('locationId', () => this.refreshAllSystemQty());
            this.$nextTick(() => this.refreshAllSystemQty());
        },
        async fetchSystemQty(index) {
            const row = this.rows[index];
            if (! row.product_id || ! this.locationId) {
                row.system_quantity = '';
                return;
            }
            row.loading = true;
            try {
                const params = new URLSearchParams({
                    product_id: row.product_id,
                    location_type: this.locationType,
                    location_id: this.locationId,
                });
                const res = await fetch(`{{ route('inventory.balance') }}?${params}`);
                const data = await res.json();
                row.system_quantity = data.system_quantity ?? 0;
            } catch (e) {
                row.system_quantity = '';
            } finally {
                row.loading = false;
            }
        },
        async refreshAllSystemQty() {
            for (let i = 0; i < this.rows.length; i++) {
                await this.fetchSystemQty(i);
            }
        },
        addRow() {
            this.rows.push({ product_id: '', counted_quantity: '', system_quantity: '', loading: false });
        },
        removeRow(i) {
            if (this.rows.length > 1) this.rows.splice(i, 1);
        },
        lineVariance(row) {
            if (row.system_quantity === '' || row.counted_quantity === '') return null;
            return parseFloat(row.counted_quantity) - parseFloat(row.system_quantity);
        },
        varianceClass(row) {
            const v = this.lineVariance(row);
            if (v === null) return 'adj-variance-pending';
            if (Math.abs(v) < 0.001) return 'adj-variance-zero';
            return v > 0 ? 'adj-variance-up' : 'adj-variance-down';
        },
        varianceLabel(row) {
            const v = this.lineVariance(row);
            if (v === null) return '—';
            if (Math.abs(v) < 0.001) return '0';
            return (v > 0 ? '+' : '') + v;
        },
        linesWithVariance() {
            return this.rows.filter(r => {
                const v = this.lineVariance(r);
                return v !== null && Math.abs(v) >= 0.001;
            }).length;
        },
    }"
>
    <div class="adj-section">
        <div class="adj-section-head">
            <div>
                <p class="adj-section-title"><i class="fas fa-map-location-dot"></i> Location &amp; reason</p>
                <p class="adj-section-sub">All count lines apply to one warehouse or shop.</p>
            </div>
        </div>

        <div class="mi-form-grid">
            <div>
                <label class="mi-field-label"><i class="fas fa-map-pin"></i> Location type</label>
                <select name="location_type" class="mi-select" x-model="locationType" required>
                    <option value="warehouse">Warehouse</option>
                    <option value="shop">Shop</option>
                </select>
                <x-input-error :messages="$errors->get('location_type')" class="mt-1.5" />
            </div>
            <div>
                <label class="mi-field-label"><i class="fas fa-warehouse"></i> Location</label>
                <select x-show="locationType === 'warehouse'" x-cloak
                        :name="locationType === 'warehouse' ? 'location_id' : undefined"
                        class="mi-select" x-model="locationId" required>
                    <option value="">Select warehouse…</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                    @endforeach
                </select>
                <select x-show="locationType === 'shop'" x-cloak
                        :name="locationType === 'shop' ? 'location_id' : undefined"
                        class="mi-select" x-model="locationId" required>
                    <option value="">Select shop…</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }} ({{ $shop->code }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('location_id')" class="mt-1.5" />
            </div>
            <div>
                <label for="reason" class="mi-field-label"><i class="fas fa-tag"></i> Reason</label>
                <select id="reason" name="reason" class="mi-select" required>
                    @foreach (['count_variance' => 'Count variance', 'damaged' => 'Damaged', 'lost' => 'Lost / missing', 'correction' => 'Correction', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('reason', 'count_variance') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('reason')" class="mt-1.5" />
            </div>
        </div>

        <div class="mt-4">
            <label for="notes" class="mi-field-label"><i class="fas fa-align-left"></i> Notes for approver</label>
            <textarea id="notes" name="notes" rows="2" class="mi-input block w-full resize-y"
                      placeholder="Optional context — e.g. annual stocktake, damaged pallet, etc.">{{ old('notes') }}</textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
        </div>
    </div>

    <div class="adj-section">
        <div class="adj-lines-wrap">
            <div class="adj-lines-toolbar">
                <div>
                    <p class="adj-section-title !mb-0"><i class="fas fa-list-check"></i> Count lines</p>
                    <p class="adj-section-sub">Select products and enter physical counts.</p>
                </div>
                <button type="button" @click="addRow()" class="mi-btn-orange text-sm !py-1.5">
                    <i class="fas fa-plus text-xs"></i> Add line
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="adj-lines-table">
                    <thead>
                        <tr>
                            <th style="width:2.5rem">#</th>
                            <th>Product</th>
                            <th style="width:6rem">System</th>
                            <th style="width:7rem">Counted</th>
                            <th style="width:6.5rem">Variance</th>
                            <th style="width:2.5rem"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td><span class="adj-line-num" x-text="index + 1"></span></td>
                                <td>
                                    <select class="mi-select !text-sm" :name="`items[${index}][product_id]`"
                                            x-model="row.product_id" @change="fetchSystemQty(index)" required>
                                        <option value="">Select product…</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->part_number }} — {{ Str::limit($product->name, 40) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <span class="adj-system-qty" :class="{ 'loading': row.loading }">
                                        <span x-show="row.loading"><i class="fas fa-spinner fa-spin"></i> …</span>
                                        <span x-show="! row.loading"
                                              x-text="row.system_quantity !== '' ? Number(row.system_quantity).toLocaleString(undefined, { maximumFractionDigits: 2 }) : '—'"></span>
                                    </span>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           class="mi-input block w-full !text-sm !py-1.5"
                                           :name="`items[${index}][counted_quantity]`"
                                           x-model="row.counted_quantity"
                                           placeholder="0" required>
                                </td>
                                <td>
                                    <span class="adj-variance" :class="varianceClass(row)">
                                        <i class="fas fa-arrows-left-right text-[0.55rem]" x-show="lineVariance(row) !== null && Math.abs(lineVariance(row)) >= 0.001"></i>
                                        <span x-text="varianceLabel(row)"></span>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" @click="removeRow(index)"
                                            class="mi-action del" title="Remove line"
                                            :disabled="rows.length <= 1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="adj-summary-bar" x-show="rows.length > 0" x-cloak>
                <span><strong x-text="rows.length"></strong> line(s)</span>
                <span><strong x-text="linesWithVariance()"></strong> with variance</span>
                <span x-show="linesWithVariance() === 0" class="text-amber-800">Add at least one non-zero variance before submitting for approval.</span>
            </div>
        </div>

        <x-input-error :messages="$errors->get('items')" class="mt-2" />
        <x-input-error :messages="$errors->get('items.*')" class="mt-2" />
    </div>
</div>
