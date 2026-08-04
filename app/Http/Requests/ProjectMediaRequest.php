<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['nullable', 'array', 'max:12', 'required_without:external_url'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'external_url' => [
                'nullable', 'url:http,https', 'max:500', 'required_without:images',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value) { return; }
                    $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));
                    $allowed = ['youtube.com', 'www.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];
                    if (! in_array($host, $allowed, true)) {
                        $fail('Only YouTube and Vimeo video URLs are accepted.');
                    }
                },
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'display_size' => ['required', 'in:standard,wide,portrait'],
        ];
    }
}
