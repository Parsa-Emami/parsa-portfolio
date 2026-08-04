@php
    $siteName = $settings['name'] ?? config('portfolio.name');
    $metaTitle = trim($__env->yieldContent('title', $settings['seo_title'] ?? ($siteName.' — Developer & Digital Builder')));
    $metaDescription = trim($__env->yieldContent('description', $settings['seo_description'] ?? ($siteName.' portfolio')));
    $canonical = trim($__env->yieldContent('canonical', url()->current()));
    $ogImagePath = trim($__env->yieldContent('og_image', $settings['site_og_image'] ?? ''));
    $ogImage = $ogImagePath ? (str_starts_with($ogImagePath, 'http') ? $ogImagePath : url(Storage::url($ogImagePath))) : asset('images/portfolio-og.svg');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b0b0c">
    <meta name="color-scheme" content="dark light">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="manifest" href="{{ route('seo.manifest') }}">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <title>{{ $metaTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">

    <script nonce="{{ $cspNonce ?? '' }}">
        (() => {
            const saved = localStorage.getItem('portfolio-theme');
            const preferred = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            document.documentElement.dataset.theme = saved || preferred;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('structured-data')
</head>
<body data-portfolio-page>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="page-noise" aria-hidden="true"></div>
    <div class="page-transition" data-page-transition aria-hidden="true"></div>
    <div class="cursor-dot" data-cursor-dot aria-hidden="true"></div>
    <div class="cursor-ring" data-cursor-ring aria-hidden="true"></div>

    <header class="site-header" data-header>
        <a class="brand" href="{{ route('portfolio.index') }}" aria-label="{{ $siteName }} home" data-magnetic>
            <span class="brand-mark">PE</span>
            <span class="brand-copy">{{ $siteName }}</span>
        </a>

        <button class="menu-toggle" type="button" data-menu-toggle aria-controls="site-navigation" aria-expanded="false">
            <span>Menu</span><i></i><i></i>
        </button>

        <nav class="site-nav" id="site-navigation" data-navigation aria-label="Primary navigation">
            <a href="{{ route('portfolio.index') }}#work">Work</a>
            <a href="{{ route('portfolio.index') }}#about">About</a>
            <a href="{{ route('portfolio.index') }}#experience">Experience</a>
            <a href="{{ route('portfolio.index') }}#contact">Contact</a>
        </nav>

        <div class="header-actions">
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle color theme">
                <span class="theme-toggle-sun">☼</span><span class="theme-toggle-moon">◐</span>
            </button>
            @php($publicEmail = $settings['email'] ?? config('portfolio.email'))
            @php($githubUrl = $settings['github_url'] ?? config('portfolio.github_url'))
            <a class="availability" href="{{ $publicEmail ? 'mailto:'.$publicEmail : $githubUrl }}" @unless($publicEmail) target="_blank" rel="noreferrer" @endunless>
                <span @class(['is-offline' => ($settings['availability_enabled'] ?? '1') !== '1'])></span>
                {{ $settings['availability_text'] ?? 'Available for selected projects' }}
            </a>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="site-footer section-shell">
        <div>
            <span>{{ $settings['job_title'] ?? 'Laravel Developer & Creative Technologist' }}</span>
            <p>Designed with intent. Built with Laravel.</p>
        </div>
        <div class="footer-links">
            <a href="{{ $settings['github_url'] ?? config('portfolio.github_url') }}" target="_blank" rel="noreferrer">GitHub ↗</a>
            @if ($settings['linkedin_url'] ?? null)<a href="{{ $settings['linkedin_url'] }}" target="_blank" rel="noreferrer">LinkedIn ↗</a>@endif
            <button type="button" data-scroll-top>Back to top ↑</button>
        </div>
        <p class="footer-copy">© {{ now()->year }} {{ $siteName }}</p>
    </footer>

    @if ($settings['analytics_id'] ?? null)
        <script nonce="{{ $cspNonce ?? '' }}" async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($settings['analytics_id']) }}"></script>
        <script nonce="{{ $cspNonce ?? '' }}">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($settings['analytics_id']));</script>
    @endif
</body>
</html>
