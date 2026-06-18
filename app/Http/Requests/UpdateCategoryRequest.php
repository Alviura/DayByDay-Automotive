<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        /** @var \App\Models\Category $category */
        $category = $this->route('category');
        $exclude = array_merge([$category->id], $category->getDescendantIds());

        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn($exclude)],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'A category cannot be its own parent or a child of itself.',
        ];
    }
}
