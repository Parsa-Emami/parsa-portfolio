<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = view('seo.sitemap', [
            'projects' => Project::query()->published()->orderByDesc('updated_at')->get(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: ".route('seo.sitemap')."\n";

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function manifest(): JsonResponse
    {
        $settings = SiteSetting::values();

        return response()->json([
            'name' => $settings['name'] ?? config('portfolio.name'),
            'short_name' => $settings['name'] ?? 'Portfolio',
            'start_url' => route('portfolio.index'),
            'scope' => rtrim(route('portfolio.index'), '/').'/',
            'display' => 'standalone',
            'background_color' => '#0b0b0c',
            'theme_color' => '#d7ff3f',
        ]);
    }
}
