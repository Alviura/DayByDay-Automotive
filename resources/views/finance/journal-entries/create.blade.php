<x-app-layout title="Manual Journal">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{
        lines: @js(old('lines', [
            ['chart_of_account_id' => '', 'description' => '', 'debit' => '', 'credit' => ''],
            ['chart_of_account_id' => '', 'description' => '', 'debit' => '', 'credit' => ''],
        ])),
        addLine() { this.lines.push({ chart_of_account_id: '', description: '', debit: '', credit: '' }); },
        removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); },
        totalDebit() { return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0); },
        totalCredit() { return this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0); },
        isBalanced() { return Math.abs(this.totalDebit() - this.totalCredit()) < 0.01 && this.totalDebit() > 0; }
    }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-pen-to-square"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Manual Journal</h1>
                    <p class="text-sm text-gray-500">Balanced adjusting entry — requires approval before posting to the GL.</p>
                </div>
            </div>
            <a href="{{ route('journal-entries.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Ledger</a>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'create-journal'])

        <form method="POST" action="{{ route('journal-entries.store') }}" class="space-y-4 max-w-4xl">
            @csrf

            <div class="mi-form-card space-y-5">
                <div>
                    <h2 class="text-sm font-bold text-gray-800 mb-3"><i class="fas fa-file-lines text-gray-400 mr-1"></i> Header</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="mi-field-label">Entry date</label>
                            <input type="date" name="entry_date" class="mi-input w-full" value="{{ old('entry_date', now()->toDateString()) }}" required>
                        </div>
                        <div>
                            <label class="mi-field-label">Description</label>
                            <input type="text" name="description" class="mi-input w-full" value="{{ old('description') }}" required placeholder="e.g. Month-end accrual adjustment">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-gray-800"><i class="fas fa-table-list text-gray-400 mr-1"></i> Journal lines</h2>
                        <button type="button" class="mi-btn-ghost text-xs" @click="addLine()"><i class="fas fa-plus"></i> Add line</button>
                    </div>

                    <template x-for="(line, pi) in lines" :key="pi">
                        <div class="fin-journal-line">
                            <div>
                                <label class="mi-field-label">GL Account</label>
                                <select :name="`lines[${pi}][chart_of_account_id]`" class="mi-select w-full" x-model="line.chart_of_account_id" required>
                                    <option value="">Select account…</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mi-field-label">Debit (KES)</label>
                                <input type="number" step="0.01" min="0" class="mi-input w-full" :name="`lines[${pi}][debit]`" x-model="line.debit" placeholder="0.00">
                            </div>
                            <div>
                                <label class="mi-field-label">Credit (KES)</label>
                                <input type="number" step="0.01" min="0" class="mi-input w-full" :name="`lines[${pi}][credit]`" x-model="line.credit" placeholder="0.00">
                            </div>
                            <div>
                                <label class="mi-field-label">Line memo</label>
                                <input type="text" class="mi-input w-full" :name="`lines[${pi}][description]`" x-model="line.description" placeholder="Optional">
                            </div>
                            <button type="button" class="mi-btn-ghost text-rose-600 mb-0.5" @click="removeLine(pi)" x-show="lines.length > 2" title="Remove line">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>

                    <div class="fin-balance-bar mt-3">
                        <div class="flex flex-wrap gap-6">
                            <span class="fin-balance-stat">Debits: <strong x-text="totalDebit().toFixed(2)"></strong></span>
                            <span class="fin-balance-stat">Credits: <strong x-text="totalCredit().toFixed(2)"></strong></span>
                        </div>
                        <span :class="isBalanced() ? 'fin-balance-ok' : 'fin-balance-bad'" x-text="isBalanced() ? '✓ Balanced' : 'Must balance before save'"></span>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 space-y-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="submit_for_approval" value="1" class="rounded border-gray-300" @checked(old('submit_for_approval'))>
                        Submit for approval immediately after save
                    </label>
                    <div>
                        <label class="mi-field-label">Approval notes (optional)</label>
                        <input type="text" name="approval_notes" class="mi-input w-full" value="{{ old('approval_notes') }}" placeholder="Context for the approver…">
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-800">{{ $errors->first() }}</div>
            @endif

            <div class="flex gap-2">
                <button type="submit" class="mi-btn-orange"><i class="fas fa-check text-xs"></i> Save Journal</button>
                <a href="{{ route('journal-entries.index') }}" class="mi-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
