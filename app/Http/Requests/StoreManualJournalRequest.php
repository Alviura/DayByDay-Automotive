<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.journal');
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.chart_of_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.shop_id' => ['nullable', 'exists:shops,id'],
            'lines.*.payment_method' => ['nullable', 'in:cash,mpesa,bank_transfer,card'],
            'submit_for_approval' => ['sometimes', 'boolean'],
            'approval_notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
