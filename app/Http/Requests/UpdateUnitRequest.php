<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        $unit = $this->route('unit');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($unit),
            ],
            'abbreviation' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }
}
