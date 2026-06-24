<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.manage');
    }

    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'max:500'],
        ];
    }
}
