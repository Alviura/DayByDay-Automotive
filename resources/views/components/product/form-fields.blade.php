@props([
    'product' => null,
    'productNames' => collect(),
    'makes' => collect(),
    'modelsByMake' => collect(),
    'allModels' => collect(),
    'categories' => collect(),
    'units' => collect(),
])

@php
    $selectedFitment = old('vehicle_model_ids', $product?->fitmentModels?->pluck('id')->all() ?? []);
    $initialMakeId = old('vehicle_make_id', $product?->vehicle_make_id ?? '');
    $initialModelId = old('vehicle_model_id', $product?->vehicle_model_id ?? '');
    $showAdditionalFitment = count($selectedFitment) > 0
        || $errors->has('vehicle_model_ids')
        || $errors->has('vehicle_model_ids.*');
@endphp

<div
    x-data="{
        modelsByMake: @js($modelsByMake),
        makeId: @js($initialMakeId ? (string) $initialMakeId : ''),
        modelId: @js($initialModelId ? (string) $initialModelId : ''),
        showAdditionalFitment: @js($showAdditionalFitment),
        get models() {
            return this.makeId && this.modelsByMake[this.makeId]
                ? this.modelsByMake[this.makeId]
                : [];
        },
        onMakeChange() {
            const valid = this.models.some(m => String(m.id) === String(this.modelId));
            if (! valid) this.modelId = '';
        }
    }"
    class="space-y-6"
>
    <div class="mi-form-grid">
        <div>
            <label for="part_number" class="mi-field-label">
                <i class="fas fa-barcode"></i> Part Number
            </label>
            <x-text-input id="part_number" name="part_number" type="text" class="mi-input block w-full uppercase"
                          :value="old('part_number', $product?->part_number ?? '')" required autofocus
                          placeholder="e.g. BRK-PAD-TYT-001" />
            <p class="mi-field-hint">Unique identifier — used on POs, labels, and POS lookup.</p>
            <x-input-error :messages="$errors->get('part_number')" class="mt-1.5" />
        </div>

        <div>
            <label for="product_name_id" class="mi-field-label">
                <i class="fas fa-tags"></i> Product Name
            </label>
            <select id="product_name_id" name="product_name_id" class="mi-select" required>
                <option value="">— Select product name —</option>
                @foreach ($productNames as $productName)
                    <option value="{{ $productName->id }}" @selected(old('product_name_id', $product?->product_name_id ?? '') == $productName->id)>
                        {{ $productName->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('product_name_id')" class="mt-1.5" />
        </div>
    </div>

    <div>
        <p class="mi-field-label mb-3"><i class="fas fa-car-side"></i> Primary Vehicle Fitment</p>
        <div class="mi-form-grid">
            <div>
                <label for="vehicle_make_id" class="mi-field-label text-gray-500">Make</label>
                <select id="vehicle_make_id" name="vehicle_make_id" class="mi-select"
                        x-model="makeId" @change="onMakeChange()">
                    <option value="">Select make... </option>
                    @foreach ($makes as $make)
                        <option value="{{ $make->id }}">{{ $make->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('vehicle_make_id')" class="mt-1.5" />
            </div>
            <div>
                <label for="vehicle_model_id" class="mi-field-label text-gray-500">Model</label>
                <select id="vehicle_model_id" name="vehicle_model_id" class="mi-select"
                        x-model="modelId" :disabled="!makeId">
                    <option value="">Select model…</option>
                    <template x-for="model in models" :key="model.id">
                        <option :value="model.id" x-text="model.name"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get('vehicle_model_id')" class="mt-1.5" />
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-gray-50/60 p-4 space-y-3">
        <div class="mi-toggle-row !mb-0">
            <div class="mi-toggle-copy">
                <p class="mi-toggle-title">
                    <i class="fas fa-car text-orange-500 mr-1"></i>
                    Additional fitment (multi-model)
                </p>
                <p class="mi-toggle-desc">Optional — select other models this part fits, e.g. same pad for Premio and Allion.</p>
            </div>
            <label for="show_additional_fitment" class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="show_additional_fitment" class="mi-toggle-check"
                       x-model="showAdditionalFitment">
            </label>
        </div>

        <div x-show="showAdditionalFitment" x-cloak x-transition class="space-y-2">
            <div class="mi-fitment-grid max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3 space-y-1">
                @forelse ($allModels as $model)
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 rounded px-1 py-0.5">
                        <input type="checkbox" name="vehicle_model_ids[]" value="{{ $model['id'] }}"
                               class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                               @checked(in_array($model['id'], $selectedFitment))>
                        <span>{{ $model['name'] }}</span>
                    </label>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No vehicle models available yet.</p>
                @endforelse
            </div>
            <x-input-error :messages="$errors->get('vehicle_model_ids')" class="mt-1.5" />
            <x-input-error :messages="$errors->get('vehicle_model_ids.*')" class="mt-1.5" />
        </div>
    </div>

    @php
        $unitSellMeta = $units->mapWithKeys(fn ($unit) => [
            $unit->id => [
                'sell_as' => $unit->supplierSellAs()?->value,
                'default_pieces' => $unit->supplierSellAs()?->defaultUnitsPerUnit() ?? 1,
            ],
        ]);
    @endphp

    <div
        x-data="{
            unitId: @js((string) old('unit_id', $product?->unit_id ?? '')),
            piecesPerUnit: @js((float) old('units_per_supplier_unit', $product?->units_per_supplier_unit ?? 1)),
            unitMeta: @js($unitSellMeta),
            get sellAs() {
                const meta = this.unitMeta[this.unitId];
                return meta?.sell_as || 'piece';
            },
            get piecesLabel() {
                if (this.sellAs === 'pair') return 'Pieces per pair';
                if (this.sellAs === 'set') return 'Pieces per set';
                return 'Stock pieces per unit';
            },
            onUnitChange() {
                const meta = this.unitMeta[this.unitId];
                if (! meta?.sell_as) {
                    this.piecesPerUnit = 1;
                    return;
                }
                this.piecesPerUnit = meta.default_pieces;
            }
        }"
        class="mi-form-grid"
    >
        <div>
            <label for="category_id" class="mi-field-label">
                <i class="fas fa-folder-tree"></i> Category
            </label>
            <select id="category_id" name="category_id" class="mi-select">
                <option value="">— None —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id ?? '') == $category->id)>
                        {{ $category->parent ? $category->parent->name.' › ' : '' }}{{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
        </div>

        <div>
            <label for="unit_id" class="mi-field-label">
                <i class="fas fa-ruler-combined"></i> Unit of Measure
            </label>
            <select id="unit_id" name="unit_id" class="mi-select" x-model="unitId" @change="onUnitChange()">
                <option value="">— None —</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_id', $product?->unit_id ?? '') == $unit->id)>
                        {{ $unit->name }}{{ $unit->abbreviation ? ' ('.$unit->abbreviation.')' : '' }}
                    </option>
                @endforeach
            </select>
            <p class="mi-field-hint">How this item is stocked, ordered, and sold (piece, pair, or set).</p>
            <x-input-error :messages="$errors->get('unit_id')" class="mt-1.5" />
        </div>

        <div x-show="sellAs !== 'piece'" x-cloak>
            <label for="units_per_supplier_unit" class="mi-field-label text-gray-500">
                <span x-text="piecesLabel"></span>
            </label>
            <x-text-input id="units_per_supplier_unit" type="number" min="1" step="1"
                          class="mi-input block w-full" x-model.number="piecesPerUnit"
                          x-bind:readonly="sellAs === 'pair'" />
            <p class="mi-field-hint" x-show="sellAs === 'pair'">Pairs always contain 2 stock pieces.</p>
            <p class="mi-field-hint" x-show="sellAs === 'set'">How many stock pieces are in one set (e.g. 4 for brake pads).</p>
            <x-input-error :messages="$errors->get('units_per_supplier_unit')" class="mt-1.5" />
        </div>

        <input type="hidden" name="supplier_sell_as" x-bind:value="sellAs">
        <input type="hidden" name="units_per_supplier_unit" x-bind:value="sellAs === 'piece' ? 1 : piecesPerUnit">
    </div>

    <div
        x-data="{
            width: @js(old('width', $product?->width ?? '') !== '' && old('width', $product?->width) !== null ? (string) old('width', $product?->width) : ''),
            length: @js(old('length', $product?->length ?? '') !== '' && old('length', $product?->length) !== null ? (string) old('length', $product?->length) : ''),
            height: @js(old('height', $product?->height ?? '') !== '' && old('height', $product?->height) !== null ? (string) old('height', $product?->height) : ''),
            qtyPerPacket: @js(old('quantity_per_packet', $product?->quantity_per_packet ?? 1)),
            get cbmPerPacket() {
                const w = parseFloat(this.width) || 0;
                const l = parseFloat(this.length) || 0;
                const h = parseFloat(this.height) || 0;
                if (!w || !l || !h) return null;
                return (w * l * h).toFixed(6);
            },
            get hasDimensions() {
                return this.cbmPerPacket !== null;
            }
        }"
        class="rounded-lg border border-sky-200 bg-sky-50/50 p-4 space-y-4"
    >
        <div>
            <p class="mi-field-label mb-1">
                <i class="fas fa-ruler"></i> Packaging &amp; CBM (import freight)
            </p>
            <p class="mi-field-hint">
                Measure the <strong>shipping packet</strong> with calipers/ruler (metres).
                Qty per packet = stock pieces in one measured box (usually matches pieces per pair/set).
                Import freight is charged <strong>per packet</strong> for pairs/sets.
            </p>
        </div>

        <div class="mi-form-grid">
            <div>
                <label for="width" class="mi-field-label text-gray-500">Width (m)</label>
                <x-text-input id="width" name="width" type="number" min="0" step="0.0001"
                              class="mi-input block w-full" x-model="width" placeholder="e.g. 0.45" />
                <x-input-error :messages="$errors->get('width')" class="mt-1.5" />
            </div>
            <div>
                <label for="length" class="mi-field-label text-gray-500">Length (m)</label>
                <x-text-input id="length" name="length" type="number" min="0" step="0.0001"
                              class="mi-input block w-full" x-model="length" placeholder="e.g. 0.02" />
                <x-input-error :messages="$errors->get('length')" class="mt-1.5" />
            </div>
            <div>
                <label for="height" class="mi-field-label text-gray-500">Height (m)</label>
                <x-text-input id="height" name="height" type="number" min="0" step="0.0001"
                              class="mi-input block w-full" x-model="height" placeholder="e.g. 0.12" />
                <x-input-error :messages="$errors->get('height')" class="mt-1.5" />
            </div>
            <div>
                <label for="quantity_per_packet" class="mi-field-label text-gray-500">Qty per packet</label>
                <x-text-input id="quantity_per_packet" name="quantity_per_packet" type="number" min="0.01" step="0.01"
                              class="mi-input block w-full" x-model.number="qtyPerPacket" />
                <p class="mi-field-hint">Units inside one measured pack (e.g. 2 for a wiper pair).</p>
                <x-input-error :messages="$errors->get('quantity_per_packet')" class="mt-1.5" />
            </div>
        </div>

        <div x-show="hasDimensions" x-cloak class="flex flex-wrap items-center gap-3 rounded-lg border border-sky-200 bg-white px-3 py-2.5 text-sm">
            <span class="font-semibold text-sky-800">
                <i class="fas fa-cube text-sky-500 mr-1"></i>
                CBM per packet:
            </span>
            <span class="font-mono font-bold text-sky-900" x-text="cbmPerPacket"></span>
            <span class="text-gray-400">m³</span>
            <span class="text-gray-500 text-xs" x-show="qtyPerPacket > 1" x-cloak>
                · <span x-text="qtyPerPacket"></span> stock pieces per CBM packet
            </span>
        </div>
    </div>

    <div class="mi-form-grid">
        <div>
            <label for="cost_price" class="mi-field-label">
                <i class="fas fa-coins"></i> Currrent Cost Price
            </label>
            <x-text-input id="cost_price" name="cost_price" type="number" min="0" step="0.01"
                          class="mi-input block w-full" :value="old('cost_price', $product?->cost_price ?? '0')" />
            <x-input-error :messages="$errors->get('cost_price')" class="mt-1.5" />
        </div>

        <div>
            <label for="min_selling_price" class="mi-field-label">
                <i class="fas fa-tag"></i> Min Selling Price
            </label>
            <x-text-input id="min_selling_price" name="min_selling_price" type="number" min="0" step="0.01"
                          class="mi-input block w-full" :value="old('min_selling_price', $product?->min_selling_price ?? '0')" />
            <x-input-error :messages="$errors->get('min_selling_price')" class="mt-1.5" />
        </div>

        <div>
            <label for="max_selling_price" class="mi-field-label">
                <i class="fas fa-tags"></i> Max Selling Price
            </label>
            <x-text-input id="max_selling_price" name="max_selling_price" type="number" min="0" step="0.01"
                          class="mi-input block w-full" :value="old('max_selling_price', $product?->max_selling_price ?? '0')" />
            <p class="mi-field-hint">POS defaults to max price; cashier can adjust within this range.</p>
            <x-input-error :messages="$errors->get('max_selling_price')" class="mt-1.5" />
        </div>

        <div>
            <label for="reorder_level" class="mi-field-label">
                <i class="fas fa-bell"></i> Reorder Level
            </label>
            <x-text-input id="reorder_level" name="reorder_level" type="number" min="0"
                          class="mi-input block w-full" :value="old('reorder_level', $product?->reorder_level ?? '0')" />
            <p class="mi-field-hint">Minimum stock before a low-stock alert (inventory module).</p>
            <x-input-error :messages="$errors->get('reorder_level')" class="mt-1.5" />
        </div>
    </div>

    <div>
        <label for="description" class="mi-field-label">
            <i class="fas fa-align-left"></i> Description
        </label>
        <textarea id="description" name="description" rows="3" class="mi-input block w-full resize-y"
                  placeholder="Optional notes, specs, or fitment details…">{{ old('description', $product?->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
    </div>

    <div class="mi-toggle-row">
        <div class="mi-toggle-copy">
            <p class="mi-toggle-title">Active status</p>
            <p class="mi-toggle-desc">When active, this product appears in POS search and can receive stock.</p>
        </div>
        <label for="is_active" class="inline-flex items-center cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   class="mi-toggle-check"
                   @checked(old('is_active', $product?->is_active ?? true))>
        </label>
    </div>
</div>
