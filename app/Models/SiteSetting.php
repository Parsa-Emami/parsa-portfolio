<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'group',
        'type',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    public static function definitions(): array
    {
        return [
            'name' => [
                'label' => 'نام نمایشی',
                'group' => 'هویت',
                'type' => 'text',
                'default' => config('portfolio.name', 'Parsa Emami'),
            ],
            'location' => [
                'label' => 'موقعیت یا نوع همکاری',
                'group' => 'هویت',
                'type' => 'text',
                'default' => config('portfolio.location', 'Tehran · Remote'),
            ],
            'availability_text' => [
                'label' => 'متن وضعیت همکاری',
                'group' => 'هویت',
                'type' => 'text',
                'default' => 'Available for selected work',
            ],
            'hero_line_1' => [
                'label' => 'خط اول عنوان اصلی',
                'group' => 'صفحه اصلی',
                'type' => 'text',
                'default' => 'I build digital',
            ],
            'hero_line_2' => [
                'label' => 'خط دوم عنوان اصلی',
                'group' => 'صفحه اصلی',
                'type' => 'text',
                'default' => 'worlds & useful',
            ],
            'hero_line_3' => [
                'label' => 'خط سوم عنوان اصلی',
                'group' => 'صفحه اصلی',
                'type' => 'text',
                'default' => 'products.',
            ],
            'intro' => [
                'label' => 'معرفی کوتاه',
                'group' => 'صفحه اصلی',
                'type' => 'textarea',
                'default' => 'Laravel developer and creative technologist focused on expressive interfaces, maintainable systems and products people enjoy using.',
            ],
            'about_heading' => [
                'label' => 'عنوان بخش درباره من',
                'group' => 'درباره من',
                'type' => 'textarea',
                'default' => 'I connect visual ideas to backend systems that can actually support them.',
            ],
            'about_body' => [
                'label' => 'متن درباره من',
                'group' => 'درباره من',
                'type' => 'textarea',
                'default' => 'My work sits between product thinking, interface engineering and backend architecture. I enjoy turning ambitious visual concepts into reliable Laravel applications.',
            ],
            'core_stack' => [
                'label' => 'تکنولوژی‌های اصلی',
                'group' => 'درباره من',
                'type' => 'textarea',
                'default' => 'Laravel, PHP, Blade, Livewire, MySQL, JavaScript, Vite',
            ],
            'focus' => [
                'label' => 'حوزه تمرکز',
                'group' => 'درباره من',
                'type' => 'textarea',
                'default' => 'Portfolio experiences, web products, internal tools and interactive systems',
            ],
            'approach' => [
                'label' => 'رویکرد کاری',
                'group' => 'درباره من',
                'type' => 'textarea',
                'default' => 'Clear architecture, deliberate motion, measured performance and maintainable code',
            ],
            'contact_heading' => [
                'label' => 'عنوان تماس',
                'group' => 'ارتباط',
                'type' => 'textarea',
                'default' => 'Have an idea worth building properly?',
            ],
            'email' => [
                'label' => 'ایمیل عمومی',
                'group' => 'ارتباط',
                'type' => 'email',
                'default' => config('portfolio.email'),
            ],
            'github_url' => [
                'label' => 'لینک GitHub',
                'group' => 'ارتباط',
                'type' => 'url',
                'default' => config('portfolio.github_url', 'https://github.com/Parsa-Emami'),
            ],
            'linkedin_url' => [
                'label' => 'لینک LinkedIn',
                'group' => 'ارتباط',
                'type' => 'url',
                'default' => null,
            ],
        ];
    }

    public static function values(): array
    {
        $defaults = collect(static::definitions())
            ->mapWithKeys(fn (array $definition, string $key) => [$key => $definition['default'] ?? null])
            ->all();

        $stored = Cache::rememberForever('portfolio.site-settings', fn () => static::query()
            ->pluck('value', 'key')
            ->all());

        return array_replace($defaults, $stored);
    }

    public static function forgetCache(): void
    {
        Cache::forget('portfolio.site-settings');
    }
}
