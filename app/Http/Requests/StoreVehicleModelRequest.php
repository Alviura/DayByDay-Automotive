<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        return [
            'vehicle_make_id' => ['required', 'exists:vehicle_makes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_models', 'name')->where('vehicle_make_id', $this->vehicle_make_id),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
