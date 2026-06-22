@props(['isEdit' => false])

<div class="space-y-4 text-sm text-gray-600">
    <p class="font-semibold text-gray-800">Employee guide</p>
    <ul class="space-y-2 list-disc pl-4">
        <li><strong>Drivers</strong> — set station to Field / Mobile; no system login needed.</li>
        <li><strong>Shop staff</strong> — assign a shop and optionally create a login with Shop Manager or Shop Attendant role.</li>
        <li><strong>Salary</strong> — monthly basic + allowances; statutory deductions are applied at payroll run time.</li>
        <li>Enter KRA PIN, NSSF, and SHIF numbers for accurate payroll records.</li>
    </ul>
    @unless($isEdit)
        <p class="text-xs text-gray-400">Employee number is assigned automatically on save.</p>
    @endunless
</div>
