<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer_accounts.manage');
    }

    public function rules(): array
    {
        return (new StoreCustomerAccountRequest)->rules();
    }
}
