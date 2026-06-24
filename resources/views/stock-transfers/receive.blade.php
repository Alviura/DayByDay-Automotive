@php
    $receivableItems = $stockTransfer->items->filter(fn ($item) => $item->remainingQuantity() > 0);
    $totalRemaining = $receivableItems->sum(fn ($item) => $item->remainingQuantity());
    $totalDispatched = (float) $stockTransfer->items->sum('dispatched_quantity');
    $totalReceived = (float) $stockTransfer->items->sum('received_quantity');
    $receivePct = $totalDispatched > 0 ? (int) round(($totalReceived / $totalDispatched) * 100) : 0;
    $isPartialReceipt = $totalReceived > 0 && $totalRemaining > 0;
@endphp

<x-app-layout :title="'Receive '.$stockTransfer->transfer_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5"
         x-data="{
            fillRemaining() {
                this.$refs.receiptTable.querySelectorAll('[data-field=received]').forEach(input => {
                    input.value = input.dataset.max;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
            },
            zeroDamaged() {
                this.$refs.receiptTable.querySelectorAll('[data-field=damaged]').forEach(input => {
                    input.value = 0;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
            },
            rowGood(received, damaged, max) {
                const r = parseFloat(received) || 0;
                const d = parseFloat(damaged) || 0;
                const good = Math.max(0, r - d);
                if (r > max + 0.001) return { value: good, state: 'error' };
                if (d > 0) return { value: good, state: 'warn' };
                return { value: good, state: 'ok' };
            },
            totals() {
                let received = 0, damaged = 0, good = 0;
                this.$refs.receiptTable?.querySelectorAll('tbody tr').forEach(row => {
                    const r = parseFloat(row.querySelector('[data-field=received]')?.value) || 0;
                    const d = parseFloat(row.querySelector('[data-field=damaged]')?.value) || 0;
                    received += r;
                    damaged += d;
                    good += Math.max(0, r - d);
                });
                return { received, damaged, good };
            },
            summary: { received: 0, damaged: 0, good: 0 },
            updateSummary() {
                this.summary = this.totals();
            }
         }"
         x-init="updateSummary()">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#059669;border-color:#a7f3d0">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Receive Transfer</h1>
                        <span class="tr-receive-badge">Post to Inventory</span>
                        @if ($isPartialReceipt)
                            <span class="tr-badge tr-badge-amber">Partial receipt</span>
                        @endif
                    </div>
                    <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <a href="{{ route('stock-transfers.show', $stockTransfer) }}" class="font-mono text-xs font-bold text-indigo-600 hover:text-orange-600">
                            {{ $stockTransfer->transfer_number }}
                        </a>
                        <span class="tr-route">
                            <span class="tr-route-node {{ $stockTransfer->isWarehouseSource() ? 'tr-route-node-wh' : 'tr-route-node-sh' }}">
                                <i class="fas {{ $stockTransfer->isWarehouseSource() ? 'fa-warehouse' : 'fa-store' }} text-[.6rem]"></i>
                                <span>{{ $stockTransfer->source?->name ?? 'Source' }}</span>
                            </span>
                            <i class="fas fa-arrow-right tr-route-arrow"></i>
                            <span class="tr-route-node {{ $stockTransfer->isWarehouseDestination() ? 'tr-route-node-wh' : 'tr-route-node-sh' }}">
                                <i class="fas {{ $stockTransfer->isWarehouseDestination() ? 'fa-warehouse' : 'fa-store' }} text-[.6rem]"></i>
                                <span>{{ $stockTransfer->destination?->name ?? 'Destination' }}</span>
                            </span>
                        </span>
                    </p>
                </div>
            </div>
            <a href="{{ route('stock-transfers.show', $stockTransfer) }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Transfer
            </a>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Lines to Receive</p>
                    <p class="mi-kpi-value">{{ $receivableItems->count() }}</p>
                    <p class="tr-receive-kpi-sub">With remaining quantity</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Remaining Units</p>
                    <p class="mi-kpi-value">{{ number_format($totalRemaining, 0) }}</p>
                    <p class="tr-receive-kpi-sub">Still in transit</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-box"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Receipt Progress</p>
                    <p class="mi-kpi-value">{{ $receivePct }}%</p>
                    <p class="tr-receive-kpi-sub">Already received on this transfer</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Dispatched</p>
                    <p class="mi-kpi-value">{{ number_format($totalDispatched, 0) }}</p>
                    <p class="tr-receive-kpi-sub">{{ $stockTransfer->typeLabel() }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>

        <div class="tr-receive-banner">
            <i class="fas fa-lightbulb"></i>
            <div>
                <strong>Confirm receipt at {{ $stockTransfer->destinationLabel() }}.</strong>
                Enter received and damaged quantities per line. Only good stock (received minus damaged) is added to destination inventory. You can post a partial receipt if not everything arrived.
            </div>
        </div>

        @if ($errors->any())
            <div class="tr-banner tr-banner-info" style="background:#fef2f2;border-color:#fecaca;color:#be123c">
                <i class="fas fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Form + guide --}}
        <div class="mi-form-split">
            <form method="POST"
                  action="{{ route('stock-transfers.receive.store', $stockTransfer) }}"
                  class="mi-card mi-form-main"
                  x-ref="receiptForm">
                @csrf
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-clipboard-check text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Receipt Entry</span>
                    </div>
                    <span class="mi-cat-badge">{{ $stockTransfer->destinationLabel() }}</span>
                </div>

                <div class="mi-form-body space-y-0">
                    <div class="p-4 border-b border-gray-100">
                        <label class="mi-field-label">
                            <i class="fas fa-note-sticky"></i> Receipt notes
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea name="notes" rows="2" class="mi-input block w-full"
                                  placeholder="Condition notes, delivery reference, discrepancies…">{{ old('notes') }}</textarea>
                    </div>

                    <div class="tr-receive-toolbar">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-circle-info text-orange-400"></i>
                            Good qty = received − damaged. Damaged units are not posted to stock.
                        </p>
                        <div class="tr-receive-toolbar-actions">
                            <button type="button" class="mi-btn-ghost text-xs py-1.5" @click="fillRemaining(); updateSummary()">
                                <i class="fas fa-fill-drip text-[.65rem]"></i> Fill remaining
                            </button>
                            <button type="button" class="mi-btn-ghost text-xs py-1.5" @click="zeroDamaged(); updateSummary()">
                                <i class="fas fa-eraser text-[.65rem]"></i> Clear damaged
                            </button>
                        </div>
                    </div>

                    <div class="mi-table-wrap" x-ref="receiptTable">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Dispatched</th>
                                    @if ($isPartialReceipt)
                                        <th class="text-right">Received</th>
                                    @endif
                                    <th class="text-right">Remaining</th>
                                    <th class="text-right">Receive Qty</th>
                                    <th class="text-right">Damaged</th>
                                    <th class="text-right">Good Qty</th>
                                </tr>
                            </thead>
                            <tbody @input="updateSummary()">
                                @php $formIndex = 0; @endphp
                                @foreach ($stockTransfer->items as $item)
                                    @if ($item->remainingQuantity() > 0)
                                        @php
                                            $remaining = $item->remainingQuantity();
                                            $oldReceived = old('items.'.$formIndex.'.received_quantity', $remaining);
                                            $oldDamaged = old('items.'.$formIndex.'.damaged_quantity', 0);
                                        @endphp
                                        <tr x-data="{
                                                received: '{{ $oldReceived }}',
                                                damaged: '{{ $oldDamaged }}',
                                                max: {{ $remaining }},
                                                get good() { return $root.rowGood(this.received, this.damaged, this.max); }
                                             }"
                                            :class="{ 'tr-receive-row-warn': parseFloat(damaged) > 0 }">
                                            <td>
                                                <p class="text-sm font-semibold text-gray-800">{{ $item->product->part_number }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                                @if ($item->product->unit)
                                                    <span class="mi-cat-badge mt-1">{{ $item->product->unit->abbreviation }}</span>
                                                @endif
                                                <input type="hidden" name="items[{{ $formIndex }}][product_id]" value="{{ $item->product_id }}">
                                            </td>
                                            <td class="text-right font-medium text-gray-600 tabular-nums">
                                                {{ number_format($item->dispatched_quantity, 2) }}
                                            </td>
                                            @if ($isPartialReceipt)
                                                <td class="text-right text-gray-500 tabular-nums">
                                                    {{ number_format($item->received_quantity, 2) }}
                                                </td>
                                            @endif
                                            <td class="text-right font-semibold text-gray-800 tabular-nums">
                                                {{ number_format($remaining, 2) }}
                                            </td>
                                            <td class="text-right">
                                                <input type="number" step="0.01" min="0"
                                                       name="items[{{ $formIndex }}][received_quantity]"
                                                       data-field="received"
                                                       data-max="{{ $remaining }}"
                                                       class="mi-input tr-receive-qty-input ml-auto"
                                                       x-model="received"
                                                       required>
                                            </td>
                                            <td class="text-right">
                                                <input type="number" step="0.01" min="0"
                                                       name="items[{{ $formIndex }}][damaged_quantity]"
                                                       data-field="damaged"
                                                       class="mi-input tr-receive-qty-input ml-auto"
                                                       :class="{ 'has-damage': parseFloat(damaged) > 0 }"
                                                       x-model="damaged">
                                            </td>
                                            <td class="text-right">
                                                <span class="tr-receive-good"
                                                      :class="{ warn: good.state === 'warn', error: good.state === 'error' }"
                                                      x-text="good.value.toFixed(2)"></span>
                                                <template x-if="good.state === 'error'">
                                                    <p class="text-[.62rem] text-rose-600 mt-0.5">Exceeds remaining</p>
                                                </template>
                                            </td>
                                        </tr>
                                        @php $formIndex++; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tr-receive-summary">
                        <div class="tr-receive-summary-stat">
                            <span>Receiving now</span>
                            <strong x-text="summary.received.toFixed(2)">0</strong>
                        </div>
                        <div class="tr-receive-summary-stat">
                            <span>Damaged</span>
                            <strong x-text="summary.damaged.toFixed(2)" style="color:#b45309">0</strong>
                        </div>
                        <div class="tr-receive-summary-stat">
                            <span>Good stock to post</span>
                            <strong x-text="summary.good.toFixed(2)" style="color:#15803d">0</strong>
                        </div>
                    </div>
                </div>

                <div class="mi-form-actions">
                    <x-input-error :messages="$errors->get('items')" class="mr-auto" />
                    <a href="{{ route('stock-transfers.show', $stockTransfer) }}" class="mi-btn-ghost">Cancel</a>
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-check text-xs"></i> Post Receipt to Inventory
                    </button>
                </div>
            </form>

            <x-module.form-guide subtitle="Receiving a stock transfer">
                <div class="tr-receive-transfer-card">
                    <p class="tr-receive-transfer-label">Transfer</p>
                    <p class="text-sm font-bold text-blue-900">{{ $stockTransfer->transfer_number }}</p>
                    <p class="text-xs text-blue-700 mt-1">{{ $stockTransfer->routeLabel() }}</p>
                    @if ($stockTransfer->dispatched_at)
                        <p class="text-xs text-blue-600 mt-1">
                            Dispatched {{ $stockTransfer->dispatched_at->format('d M Y, H:i') }}
                            @if ($stockTransfer->dispatcher)
                                · {{ $stockTransfer->dispatcher->name }}
                            @endif
                        </p>
                    @endif
                </div>

                <ol class="mi-guide-list">
                    <li>Count physical stock against each line at the destination.</li>
                    <li>Use <strong>Fill remaining</strong> if everything arrived intact.</li>
                    <li>Enter <strong>receive qty</strong> for units accepted today.</li>
                    <li>Record <strong>damaged</strong> units separately — they won't hit inventory.</li>
                    <li>Partial receipts are allowed; remaining units stay in transit.</li>
                </ol>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">After posting</p>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        You'll return to the transfer detail page. Good quantities are added to
                        <strong>{{ $stockTransfer->destination?->name }}</strong>.
                        If the transfer is fully received, it will be marked complete.
                    </p>
                </div>
            </x-module.form-guide>
        </div>
    </div>
</x-app-layout>
