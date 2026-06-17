<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        $vehicleModel = $this->route('vehicle_model');

        return [
            'vehicle_make_id' => ['required', 'exists:vehicle_makes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_models', 'name')
                    ->where('vehicle_make_id', $this->vehicle_make_id)
                    ->ignore($vehicleModel),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
