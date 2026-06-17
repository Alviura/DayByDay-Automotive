<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleMakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:vehicle_makes,name'],
            'is_active' => ['boolean'],
        ];
    }
}
