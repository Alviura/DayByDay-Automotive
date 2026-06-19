<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispatchTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transfers.dispatch');
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
