<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewTransferRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transfer_requests.review');
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
