<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.run');
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
