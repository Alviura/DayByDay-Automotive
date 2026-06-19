<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunCostAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'total_freight' => ['nullable', 'numeric', 'min:0'],
            'total_tax' => ['nullable', 'numeric', 'min:0'],
            'default_margin' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ];
    }
}
