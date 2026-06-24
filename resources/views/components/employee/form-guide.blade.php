@props(['isEdit' => false])

<aside class="mi-guide no-print">
    <div class="mi-guide-head">
        <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
        <div>
            <h2 class="mi-guide-title">Employee Guide</h2>
            <p class="mi-guide-subtitle">{{ $isEdit ? 'Updating records' : 'Getting started' }}</p>
        </div>
    </div>
    <div class="mi-guide-body text-sm text-gray-600 space-y-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">By role</p>
            <ul class="space-y-2">
                <li class="flex gap-2"><i class="fas fa-car-side text-green-600 mt-0.5 text-xs"></i><span><strong class="text-gray-800">Drivers</strong> — Field / Mobile station; usually no system login.</span></li>
                <li class="flex gap-2"><i class="fas fa-store text-orange-600 mt-0.5 text-xs"></i><span><strong class="text-gray-800">Shop staff</strong> — Assign a shop; optional POS login as Shop Manager or Attendant.</span></li>
                <li class="flex gap-2"><i class="fas fa-warehouse text-blue-600 mt-0.5 text-xs"></i><span><strong class="text-gray-800">Warehouse staff</strong> — Assign a warehouse; optional login as Warehouse Manager.</span></li>
            </ul>
        </div>
        <div class="border-t border-gray-100 pt-3">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Payroll</p>
            <p>Enter monthly basic + allowances. PAYE, NSSF, SHIF, and housing levy are calculated when payroll is run.</p>
            <p class="mt-2 text-xs text-gray-400">KRA PIN, NSSF, and SHIF numbers are required for statutory remittance.</p>
        </div>
        @unless($isEdit)
            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs text-gray-400"><i class="fas fa-hashtag"></i> Employee number is assigned automatically on save.</p>
            </div>
        @else
            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                    Changing salary creates a new effective salary record when amounts differ from the current package.
                </p>
            </div>
        @endunless
    </div>
</aside>
