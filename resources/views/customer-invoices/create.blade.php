<x-app-layout title="Generate Invoice">

    @push('styles')
        <x-module.page-index-styles />
        @include('customer-invoices.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Generate Invoice</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Bundle uninvoiced credit sales into a monthly statement.</p>
                </div>
            </div>
            <a href="{{ route('customer-invoices.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Invoices
            </a>
        </div>

        <div class="mi-form-split">
            <div class="space-y-5 mi-form-main">

                {{-- Period selector --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-sliders text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Invoice parameters</span>
                        </div>
                    </div>
                    <form method="GET" class="mi-form-body">
                        <div class="mi-form-grid">
                            <div class="mi-span-full">
                                <label for="account_id" class="mi-field-label"><i class="fas fa-bus"></i> Fleet account</label>
                                <select id="account_id" name="account_id" class="mi-select block w-full" onchange="this.form.submit()">
                                    <option value="">Select account…</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" @selected($selectedAccount?->id === $acc->id)>{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="period_start" class="mi-field-label"><i class="fas fa-calendar"></i> Period from</label>
                                <input type="date" id="period_start" name="period_start" value="{{ $periodStart }}" class="mi-input block w-full" onchange="this.form.submit()">
                            </div>
                            <div>
                                <label for="period_end" class="mi-field-label"><i class="fas fa-calendar"></i> Period to</label>
                                <input type="date" id="period_end" name="period_end" value="{{ $periodEnd }}" class="mi-input block w-full" onchange="this.form.submit()">
                            </div>
                        </div>
                    </form>
                </div>

                @if ($selectedAccount)
                    @php $previewTotal = $previewSales->sum('total'); @endphp

                    {{-- Preview summary --}}
                    <div class="mi-kpi-row !grid-cols-3">
                        <div class="mi-kpi mi-kpi-purple">
                            <div>
                                <p class="mi-kpi-label">Account</p>
                                <p class="mi-kpi-value text-status !text-base">{{ Str::limit($selectedAccount->name, 18) }}</p>
                            </div>
                            <div class="mi-kpi-icon"><i class="fas fa-bus"></i></div>
                        </div>
                        <div class="mi-kpi mi-kpi-amber">
                            <div>
                                <p class="mi-kpi-label">Sales in period</p>
                                <p class="mi-kpi-value">{{ $previewSales->count() }}</p>
                            </div>
                            <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
                        </div>
                        <div class="mi-kpi mi-kpi-orange">
                            <div>
                                <p class="mi-kpi-label">Invoice total</p>
                                <p class="mi-kpi-value orange">{{ number_format($previewTotal, 0) }}</p>
                                <p class="ci-kpi-sub">KES</p>
                            </div>
                            <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
                        </div>
                    </div>

                    {{-- Preview table + generate --}}
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Sales preview</p>
                                <p class="text-xs text-gray-400 mt-0.5">Uninvoiced credit sales within the selected period</p>
                            </div>
                            @if ($previewSales->isNotEmpty())
                                <span class="ci-badge ci-badge-amber">{{ $previewSales->count() }} ready</span>
                            @endif
                        </div>

                        @if ($previewSales->isEmpty())
                            <div class="mi-show-empty">
                                <i class="fas fa-receipt"></i>
                                <p>No uninvoiced credit sales in this period.</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting the date range or check the account has pending sales.</p>
                            </div>
                        @else
                            <div class="mi-table-wrap">
                                <table class="mi-table">
                                    <thead>
                                        <tr>
                                            <th>Receipt</th>
                                            <th>Date</th>
                                            <th>Vehicle</th>
                                            <th>Lines</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($previewSales as $sale)
                                            <tr>
                                                <td><span class="ci-inv-num">{{ $sale->receipt_number }}</span></td>
                                                <td class="text-sm text-gray-500">{{ $sale->sold_at?->format('d M Y') }}</td>
                                                <td>
                                                    @if ($sale->vehicle_plate)
                                                        <span class="ci-plate">{{ $sale->vehicle_plate }}</span>
                                                    @else
                                                        <span class="text-gray-300">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-sm">{{ $sale->items_count }}</td>
                                                <td class="font-bold text-orange-700">{{ number_format($sale->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-amber-50/60">
                                            <td colspan="4" class="text-right text-sm font-semibold text-gray-600">Invoice total</td>
                                            <td class="font-bold text-orange-700">{{ number_format($previewTotal, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="mi-card-foot">
                                <form method="POST" action="{{ route('customer-invoices.store') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="customer_account_id" value="{{ $selectedAccount->id }}">
                                    <input type="hidden" name="period_start" value="{{ $periodStart }}">
                                    <input type="hidden" name="period_end" value="{{ $periodEnd }}">
                                    <div>
                                        <label for="notes" class="mi-field-label"><i class="fas fa-note-sticky"></i> Invoice notes (optional)</label>
                                        <textarea id="notes" name="notes" rows="2" class="mi-input block w-full" placeholder="Payment instructions or special terms…">{{ old('notes') }}</textarea>
                                        <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="mi-btn-orange">
                                            <i class="fas fa-file-invoice text-xs"></i> Generate Invoice
                                        </button>
                                        <a href="{{ route('customer-accounts.show', $selectedAccount) }}" class="mi-btn-ghost">
                                            <i class="fas fa-bus text-xs"></i> View Account
                                        </a>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mi-card">
                        <div class="mi-show-empty py-16">
                            <div class="ci-empty-icon"><i class="fas fa-bus"></i></div>
                            <p class="font-semibold text-gray-600">Select a fleet account</p>
                            <p class="text-sm text-gray-400 mt-1">Choose an account above to preview uninvoiced sales for the billing period.</p>
                        </div>
                    </div>
                @endif
            </div>

            <x-customer-invoice.form-guide />
        </div>
    </div>
</x-app-layout>
