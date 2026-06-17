<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleMakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        $vehicleMake = $this->route('vehicle_make');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_makes', 'name')->ignore($vehicleMake),
            ],
            'is_active' => ['boolean'],
        ];
    }
}
