<?php

namespace App\Http\Requests;

use App\Enums\ApprovalActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approvals.act');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                ApprovalActionType::Approved->value,
                ApprovalActionType::Rejected->value,
                ApprovalActionType::Returned->value,
            ])],
            'comments' => [
                Rule::requiredIf(fn () => in_array($this->action, [
                    ApprovalActionType::Rejected->value,
                    ApprovalActionType::Returned->value,
                ], true)),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'comments.required' => 'Please provide a reason when rejecting or returning a request.',
        ];
    }
}
