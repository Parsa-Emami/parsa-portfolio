<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'proficiency' => ['nullable', 'integer', 'min:1', 'max:100'],
            'short_label' => ['nullable', 'string', 'max:12'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_published' => ['boolean'],
        ];
    }
}
