<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.manage');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:160'],
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'payment_method' => ['nullable', Rule::in(array_keys(\App\Models\Payment::methods()))],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
