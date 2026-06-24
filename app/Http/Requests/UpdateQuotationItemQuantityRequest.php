<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationItemQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'order_quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
