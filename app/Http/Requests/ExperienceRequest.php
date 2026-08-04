<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExperienceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $achievements = $this->input('achievements', []);
        if (is_string($achievements)) {
            $achievements = preg_split('/\R/', $achievements) ?: [];
            $achievements = collect($achievements)->map(fn (string $item) => trim($item))->filter()->values()->all();
        }

        $this->merge([
            'achievements' => $achievements,
            'is_current' => $this->boolean('is_current'),
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'organization' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'is_current' => ['boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'achievements' => ['nullable', 'array', 'max:20'],
            'achievements.*' => ['string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_published' => ['boolean'],
        ];
    }
}
