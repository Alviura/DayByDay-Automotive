<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }
}
