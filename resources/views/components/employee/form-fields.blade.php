@props(['employee' => null, 'shops' => [], 'warehouses' => [], 'roles' => []])

@php
    $salary = $employee?->currentSalary;
    $hasUser = (bool) ($employee?->user_id);
    $userRole = old('user_role', $employee?->user?->roles->first()?->name ?? '');
    $basic = old('basic_salary', $salary?->basic_salary ?? '');
    $housing = old('housing_allowance', $salary?->housing_allowance ?? 0);
    $transport = old('transport_allowance', $salary?->transport_allowance ?? 0);
    $other = old('other_allowance', $salary?->other_allowance ?? 0);
@endphp

<div class="space-y-0" x-data="{
    station: '{{ old('station_type', $employee->station_type ?? 'field') }}',
    createUser: {{ old('create_user', $hasUser ? 'true' : 'false') ? 'true' : 'false' }},
    basic: {{ is_numeric($basic) ? $basic : 0 }},
    housing: {{ is_numeric($housing) ? $housing : 0 }},
    transport: {{ is_numeric($transport) ? $transport : 0 }},
    other: {{ is_numeric($other) ? $other : 0 }},
    get gross() {
        return (parseFloat(this.basic) || 0)
             + (parseFloat(this.housing) || 0)
             + (parseFloat(this.transport) || 0)
             + (parseFloat(this.other) || 0);
    },
    formatKes(n) {
        return 'KES ' + n.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}">

    <div class="emp-form-section">
        <div class="emp-form-section-head">
            <span class="emp-step">1</span> Personal Details
        </div>
        <div class="emp-form-section-body">
            <div>
                <label for="first_name" class="mi-field-label"><i class="fas fa-user"></i> First Name</label>
                <x-text-input id="first_name" name="first_name" type="text" class="mi-input block w-full" :value="old('first_name', $employee->first_name ?? '')" required autofocus />
                <x-input-error :messages="$errors->get('first_name')" class="mt-1.5" />
            </div>
            <div>
                <label for="last_name" class="mi-field-label"><i class="fas fa-user"></i> Last Name</label>
                <x-text-input id="last_name" name="last_name" type="text" class="mi-input block w-full" :value="old('last_name', $employee->last_name ?? '')" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-1.5" />
            </div>
            <div>
                <label for="national_id" class="mi-field-label"><i class="fas fa-id-card"></i> National ID</label>
                <x-text-input id="national_id" name="national_id" type="text" class="mi-input block w-full" :value="old('national_id', $employee->national_id ?? '')" />
                <x-input-error :messages="$errors->get('national_id')" class="mt-1.5" />
            </div>
            <div>
                <label for="phone" class="mi-field-label"><i class="fas fa-phone"></i> Phone</label>
                <x-text-input id="phone" name="phone" type="text" class="mi-input block w-full" :value="old('phone', $employee->phone ?? '')" placeholder="+254 …" />
                <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
            </div>
            <div>
                <label for="email" class="mi-field-label"><i class="fas fa-envelope"></i> Work Email</label>
                <x-text-input id="email" name="email" type="email" class="mi-input block w-full" :value="old('email', $employee->email ?? '')" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>
            <div class="col-span-full">
                <label for="address" class="mi-field-label"><i class="fas fa-location-dot"></i> Address</label>
                <textarea id="address" name="address" rows="2" class="mi-input block w-full">{{ old('address', $employee->address ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
            </div>
        </div>
    </div>

    <div class="emp-form-section">
        <div class="emp-form-section-head">
            <span class="emp-step">2</span> Employment
        </div>
        <div class="emp-form-section-body">
            <div>
                <label for="job_title" class="mi-field-label"><i class="fas fa-briefcase"></i> Job Title</label>
                <x-text-input id="job_title" name="job_title" type="text" class="mi-input block w-full" :value="old('job_title', $employee->job_title ?? '')" required placeholder="e.g. Driver, Shop Attendant" />
                <x-input-error :messages="$errors->get('job_title')" class="mt-1.5" />
            </div>
            <div>
                <label for="employment_type" class="mi-field-label"><i class="fas fa-file-contract"></i> Employment Type</label>
                <select id="employment_type" name="employment_type" class="mi-select" required>
                    @foreach (['permanent' => 'Permanent', 'contract' => 'Contract', 'casual' => 'Casual'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('employment_type', $employee->employment_type ?? 'permanent') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('employment_type')" class="mt-1.5" />
            </div>
            <div>
                <label for="hire_date" class="mi-field-label"><i class="fas fa-calendar"></i> Hire Date</label>
                <x-text-input id="hire_date" name="hire_date" type="date" class="mi-input block w-full" :value="old('hire_date', optional($employee?->hire_date)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('hire_date')" class="mt-1.5" />
            </div>
            <div>
                <label for="termination_date" class="mi-field-label"><i class="fas fa-calendar-xmark"></i> Termination Date</label>
                <x-text-input id="termination_date" name="termination_date" type="date" class="mi-input block w-full" :value="old('termination_date', optional($employee?->termination_date)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('termination_date')" class="mt-1.5" />
            </div>
            <div>
                <label for="station_type" class="mi-field-label"><i class="fas fa-map-pin"></i> Station</label>
                <select id="station_type" name="station_type" class="mi-select" required x-model="station">
                    @foreach (['shop' => 'Shop', 'warehouse' => 'Warehouse', 'field' => 'Field / Mobile', 'head_office' => 'Head Office'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('station_type')" class="mt-1.5" />
            </div>
            <div x-show="station === 'shop'" x-cloak>
                <label for="shop_id" class="mi-field-label"><i class="fas fa-store"></i> Shop</label>
                <select id="shop_id" name="shop_id" class="mi-select">
                    <option value="">Select shop…</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}" @selected(old('shop_id', $employee->shop_id ?? '') == $shop->id)>{{ $shop->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('shop_id')" class="mt-1.5" />
            </div>
            <div x-show="station === 'warehouse'" x-cloak>
                <label for="warehouse_id" class="mi-field-label"><i class="fas fa-warehouse"></i> Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" class="mi-select">
                    <option value="">Select warehouse…</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $employee->warehouse_id ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1.5" />
            </div>
            <div class="col-span-full">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400" @checked(old('is_active', $employee->is_active ?? true))>
                    <span class="text-sm font-medium text-gray-700">Active employee</span>
                </label>
                <p class="mi-field-hint mt-1">Inactive employees are excluded from payroll runs.</p>
            </div>
        </div>
    </div>

    <div class="emp-form-section">
        <div class="emp-form-section-head">
            <span class="emp-step">3</span> Statutory IDs
        </div>
        <div class="emp-form-section-body">
            <div>
                <label for="kra_pin" class="mi-field-label">KRA PIN</label>
                <x-text-input id="kra_pin" name="kra_pin" type="text" class="mi-input block w-full" :value="old('kra_pin', $employee->kra_pin ?? '')" placeholder="A000000000X" />
                <x-input-error :messages="$errors->get('kra_pin')" class="mt-1.5" />
            </div>
            <div>
                <label for="nssf_number" class="mi-field-label">NSSF Number</label>
                <x-text-input id="nssf_number" name="nssf_number" type="text" class="mi-input block w-full" :value="old('nssf_number', $employee->nssf_number ?? '')" />
                <x-input-error :messages="$errors->get('nssf_number')" class="mt-1.5" />
            </div>
            <div>
                <label for="shif_number" class="mi-field-label">SHIF Number</label>
                <x-text-input id="shif_number" name="shif_number" type="text" class="mi-input block w-full" :value="old('shif_number', $employee->shif_number ?? '')" />
                <x-input-error :messages="$errors->get('shif_number')" class="mt-1.5" />
            </div>
        </div>
    </div>

    <div class="emp-form-section">
        <div class="emp-form-section-head">
            <span class="emp-step">4</span> Monthly Salary &amp; Payment
        </div>
        <div class="emp-form-section-body">
            <div>
                <label for="basic_salary" class="mi-field-label">Basic Salary</label>
                <x-text-input id="basic_salary" name="basic_salary" type="number" step="0.01" min="0" class="mi-input block w-full" x-model.number="basic" required />
                <x-input-error :messages="$errors->get('basic_salary')" class="mt-1.5" />
            </div>
            <div>
                <label for="housing_allowance" class="mi-field-label">Housing Allowance</label>
                <x-text-input id="housing_allowance" name="housing_allowance" type="number" step="0.01" min="0" class="mi-input block w-full" x-model.number="housing" />
                <x-input-error :messages="$errors->get('housing_allowance')" class="mt-1.5" />
            </div>
            <div>
                <label for="transport_allowance" class="mi-field-label">Transport Allowance</label>
                <x-text-input id="transport_allowance" name="transport_allowance" type="number" step="0.01" min="0" class="mi-input block w-full" x-model.number="transport" />
                <x-input-error :messages="$errors->get('transport_allowance')" class="mt-1.5" />
            </div>
            <div>
                <label for="other_allowance" class="mi-field-label">Other Allowance</label>
                <x-text-input id="other_allowance" name="other_allowance" type="number" step="0.01" min="0" class="mi-input block w-full" x-model.number="other" />
                <x-input-error :messages="$errors->get('other_allowance')" class="mt-1.5" />
            </div>
            <div class="col-span-full">
                <div class="emp-gross-bar">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-orange-800/70">Estimated Gross</p>
                        <p class="text-xs text-gray-500 mt-0.5">Basic + all allowances (before statutory deductions)</p>
                    </div>
                    <strong x-text="formatKes(gross)"></strong>
                </div>
            </div>
            <div>
                <label for="payment_method" class="mi-field-label">Payment Method</label>
                <select id="payment_method" name="payment_method" class="mi-select" required>
                    @foreach (['bank' => 'Bank Transfer', 'cash' => 'Cash', 'mpesa' => 'M-Pesa'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $salary?->payment_method ?? 'bank') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('payment_method')" class="mt-1.5" />
            </div>
            <div>
                <label for="bank_name" class="mi-field-label">Bank Name</label>
                <x-text-input id="bank_name" name="bank_name" type="text" class="mi-input block w-full" :value="old('bank_name', $salary?->bank_name ?? '')" />
                <x-input-error :messages="$errors->get('bank_name')" class="mt-1.5" />
            </div>
            <div class="col-span-full md:col-span-1">
                <label for="account_number" class="mi-field-label">Account Number</label>
                <x-text-input id="account_number" name="account_number" type="text" class="mi-input block w-full" :value="old('account_number', $salary?->account_number ?? '')" />
                <x-input-error :messages="$errors->get('account_number')" class="mt-1.5" />
            </div>
        </div>
    </div>

    <div class="emp-form-section">
        <div class="emp-form-section-head">
            <span class="emp-step">5</span> System Login
            <span class="ml-auto text-xs font-normal text-gray-400">Optional</span>
        </div>
        <div class="emp-form-section-body">
            @unless($hasUser)
                <div class="col-span-full emp-login-panel">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="create_user" value="1" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400" x-model="createUser" @checked(old('create_user'))>
                        <span class="text-sm font-medium text-gray-700">Create system login for this employee</span>
                    </label>
                    <p class="mi-field-hint mt-1">For shop managers and attendants who need POS access. Drivers typically do not need a login.</p>

                    <div x-show="createUser" x-cloak class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="user_email" class="mi-field-label">Login Email</label>
                            <x-text-input id="user_email" name="user_email" type="email" class="mi-input block w-full" :value="old('user_email')" />
                            <x-input-error :messages="$errors->get('user_email')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="user_role" class="mi-field-label">System Role</label>
                            <select id="user_role" name="user_role" class="mi-select">
                                <option value="">Select role…</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected($userRole === $role->name)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_role')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="user_password" class="mi-field-label">Password</label>
                            <x-text-input id="user_password" name="user_password" type="password" class="mi-input block w-full" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('user_password')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="user_password_confirmation" class="mi-field-label">Confirm Password</label>
                            <x-text-input id="user_password_confirmation" name="user_password_confirmation" type="password" class="mi-input block w-full" autocomplete="new-password" />
                        </div>
                    </div>
                </div>
            @else
                <div class="col-span-full emp-login-panel">
                    <p class="text-sm font-medium text-gray-700">
                        <i class="fas fa-key text-emerald-500"></i>
                        Linked login: <span class="text-gray-900">{{ $employee->user->email }}</span>
                    </p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="user_role" class="mi-field-label">System Role</label>
                            <select id="user_role" name="user_role" class="mi-select">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected($userRole === $role->name)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_role')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="user_password" class="mi-field-label">New Password (optional)</label>
                            <x-text-input id="user_password" name="user_password" type="password" class="mi-input block w-full" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('user_password')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="user_password_confirmation" class="mi-field-label">Confirm Password</label>
                            <x-text-input id="user_password_confirmation" name="user_password_confirmation" type="password" class="mi-input block w-full" autocomplete="new-password" />
                        </div>
                    </div>
                </div>
            @endunless
        </div>
    </div>
</div>
