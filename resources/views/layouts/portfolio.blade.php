<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="description" content="{{ $settings['name'] ?? config('portfolio.name') }} — Laravel developer and digital experience builder.">

    <title>@yield('title', ($settings['name'] ?? config('portfolio.name')) . ' — Developer & Digital Builder')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-portfolio-page>
    <div class="page-noise" aria-hidden="true"></div>

    <header class="site-header" data-header>
        <a class="brand" href="{{ route('portfolio.index') }}" aria-label="{{ $settings['name'] ?? config('portfolio.name') }} home">
            <span class="brand-mark">PE</span>
            <span class="brand-copy">{{ $settings['name'] ?? config('portfolio.name') }}</span>
        </a>

        <nav class="site-nav" aria-label="Primary navigation">
            <a href="{{ route('portfolio.index') }}#work">Work</a>
            <a href="{{ route('portfolio.index') }}#about">About</a>
            <a href="{{ route('portfolio.index') }}#contact">Contact</a>
        </nav>

        @php($publicEmail = $settings['email'] ?? config('portfolio.email'))
        @php($githubUrl = $settings['github_url'] ?? config('portfolio.github_url'))
        <a class="availability" href="{{ $publicEmail ? 'mailto:' . $publicEmail : $githubUrl }}" @unless($publicEmail) target="_blank" rel="noreferrer" @endunless>
            <span></span>
            {{ $settings['availability_text'] ?? 'Available for selected work' }}
        </a>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <p>Designed & built with Laravel.</p>
        <p>© {{ now()->year }} {{ $settings['name'] ?? config('portfolio.name') }}</p>
    </footer>
</body>
</html>
