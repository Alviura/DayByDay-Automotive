<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcurementFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'import_type' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'total_freight' => ['nullable', 'numeric', 'min:0'],
            'total_tax' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
