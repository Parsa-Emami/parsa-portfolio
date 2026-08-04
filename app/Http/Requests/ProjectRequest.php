<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $technologies = $this->input('technologies', []);

        if (is_string($technologies)) {
            $technologies = collect(explode(',', $technologies))
                ->map(fn (string $technology) => trim($technology))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $this->merge([
            'technologies' => $technologies,
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'remove_cover_image' => $this->boolean('remove_cover_image'),
        ]);
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:170',
                Rule::unique('projects', 'slug')->ignore($project?->getKey()),
            ],
            'eyebrow' => ['nullable', 'string', 'max:160'],
            'summary' => ['required', 'string', 'max:1500'],
            'content' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'max:160'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 2)],
            'technologies' => ['nullable', 'array', 'max:20'],
            'technologies.*' => ['string', 'max:50'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_cover_image' => ['boolean'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'github_url' => ['nullable', 'url', 'max:500'],
            'live_url' => ['nullable', 'url', 'max:500'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
