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
            $technologies = collect(explode(',', $technologies))->map(fn (string $item) => trim($item))->filter()->unique()->values()->all();
        }

        $this->merge([
            'technologies' => $technologies,
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'remove_cover_image' => $this->boolean('remove_cover_image'),
            'remove_og_image' => $this->boolean('remove_og_image'),
        ]);
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', Rule::unique('projects', 'slug')->ignore($project?->getKey())],
            'eyebrow' => ['nullable', 'string', 'max:160'],
            'summary' => ['required', 'string', 'max:1500'],
            'content' => ['nullable', 'string'],
            'challenge' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'architecture' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'max:160'],
            'client' => ['nullable', 'string', 'max:160'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 2)],
            'technologies' => ['nullable', 'array', 'max:30'],
            'technologies.*' => ['string', 'max:60'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'cover_alt' => ['nullable', 'string', 'max:255'],
            'remove_cover_image' => ['boolean'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_og_image' => ['boolean'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'github_url' => ['nullable', 'url:http,https', 'max:500'],
            'live_url' => ['nullable', 'url:http,https', 'max:500'],
            'video_url' => ['nullable', 'url:http,https', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
