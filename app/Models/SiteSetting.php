<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'label', 'value', 'group', 'type', 'sort_order'];

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    public static function definitions(): array
    {
        return [
            'name' => ['label' => 'نام نمایشی', 'group' => 'هویت', 'type' => 'text', 'default' => config('portfolio.name', 'Parsa Emami')],
            'job_title' => ['label' => 'عنوان تخصصی', 'group' => 'هویت', 'type' => 'text', 'default' => 'Laravel Developer & Creative Technologist'],
            'location' => ['label' => 'موقعیت یا نوع همکاری', 'group' => 'هویت', 'type' => 'text', 'default' => config('portfolio.location', 'Tehran · Remote')],
            'availability_text' => ['label' => 'متن وضعیت همکاری', 'group' => 'هویت', 'type' => 'text', 'default' => 'Available for selected projects'],
            'availability_enabled' => ['label' => 'وضعیت پذیرش پروژه', 'group' => 'هویت', 'type' => 'boolean', 'default' => '1'],
            'hero_line_1' => ['label' => 'خط اول عنوان اصلی', 'group' => 'صفحه اصلی', 'type' => 'text', 'default' => 'I build digital'],
            'hero_line_2' => ['label' => 'خط دوم عنوان اصلی', 'group' => 'صفحه اصلی', 'type' => 'text', 'default' => 'worlds & useful'],
            'hero_line_3' => ['label' => 'خط سوم عنوان اصلی', 'group' => 'صفحه اصلی', 'type' => 'text', 'default' => 'products.'],
            'intro' => ['label' => 'معرفی کوتاه', 'group' => 'صفحه اصلی', 'type' => 'textarea', 'default' => 'Laravel developer and creative technologist focused on expressive interfaces, maintainable systems and products people enjoy using.'],
            'manifesto' => ['label' => 'عبارت متحرک', 'group' => 'صفحه اصلی', 'type' => 'text', 'default' => 'Build clearly · Move deliberately · Stay curious'],
            'about_heading' => ['label' => 'عنوان بخش درباره من', 'group' => 'درباره من', 'type' => 'textarea', 'default' => 'I connect visual ideas to backend systems that can actually support them.'],
            'about_body' => ['label' => 'متن درباره من', 'group' => 'درباره من', 'type' => 'textarea', 'default' => 'My work sits between product thinking, interface engineering and backend architecture. I enjoy turning ambitious visual concepts into reliable Laravel applications.'],
            'core_stack' => ['label' => 'تکنولوژی‌های اصلی', 'group' => 'درباره من', 'type' => 'textarea', 'default' => 'Laravel, PHP, Blade, Livewire, MySQL, JavaScript, GSAP, Vite'],
            'focus' => ['label' => 'حوزه تمرکز', 'group' => 'درباره من', 'type' => 'textarea', 'default' => 'Portfolio experiences, web products, internal tools and interactive systems'],
            'approach' => ['label' => 'رویکرد کاری', 'group' => 'درباره من', 'type' => 'textarea', 'default' => 'Clear architecture, deliberate motion, measured performance and maintainable code'],
            'contact_heading' => ['label' => 'عنوان تماس', 'group' => 'ارتباط', 'type' => 'textarea', 'default' => 'Have an idea worth building properly?'],
            'contact_intro' => ['label' => 'توضیح تماس', 'group' => 'ارتباط', 'type' => 'textarea', 'default' => 'Share the context, timeline and what success should look like. I usually reply within two working days.'],
            'email' => ['label' => 'ایمیل عمومی', 'group' => 'ارتباط', 'type' => 'email', 'default' => config('portfolio.email')],
            'github_url' => ['label' => 'لینک GitHub', 'group' => 'ارتباط', 'type' => 'url', 'default' => config('portfolio.github_url', 'https://github.com/Parsa-Emami')],
            'linkedin_url' => ['label' => 'لینک LinkedIn', 'group' => 'ارتباط', 'type' => 'url', 'default' => null],
            'resume_file' => ['label' => 'فایل رزومه PDF', 'group' => 'ارتباط', 'type' => 'file', 'default' => null],
            'seo_title' => ['label' => 'عنوان SEO صفحه اصلی', 'group' => 'SEO', 'type' => 'text', 'default' => 'Parsa Emami — Laravel Developer & Creative Technologist'],
            'seo_description' => ['label' => 'توضیحات SEO صفحه اصلی', 'group' => 'SEO', 'type' => 'textarea', 'default' => 'Selected Laravel, web product and interactive development work by Parsa Emami.'],
            'site_og_image' => ['label' => 'تصویر اشتراک‌گذاری سایت', 'group' => 'SEO', 'type' => 'image', 'default' => null],
            'analytics_id' => ['label' => 'شناسه Analytics', 'group' => 'SEO', 'type' => 'text', 'default' => null],
        ];
    }

    public static function values(): array
    {
        $defaults = collect(static::definitions())
            ->mapWithKeys(fn (array $definition, string $key) => [$key => $definition['default'] ?? null])
            ->all();

        $stored = Cache::rememberForever('portfolio.site-settings', fn () => static::query()->pluck('value', 'key')->all());

        return array_replace($defaults, $stored);
    }

    public static function forgetCache(): void
    {
        Cache::forget('portfolio.site-settings');
    }
}
