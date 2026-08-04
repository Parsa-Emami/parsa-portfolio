<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>403 — Access denied</title>

    @if (is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root { color-scheme: dark; }
            * { box-sizing: border-box; }
            body { margin: 0; min-height: 100vh; display: grid; place-items: center;
                    background: #0b0b0c; color: #f7f7f2; font-family: system-ui, sans-serif; }
            main { width: min(720px, calc(100% - 40px)); }
            span { color: #d7ff3f; font-size: clamp(4rem, 20vw, 11rem); font-weight: 800; line-height: .8; }
            h1 { font-size: clamp(2rem, 6vw, 4.5rem); margin: 2rem 0 1rem; }
            p { color: #aaa; font-size: 1.1rem; }
            a { display: inline-block; margin-top: 1.5rem; color: #d7ff3f; }
        </style>
    @endif
</head>
<body class="error-page">
    <main>
        <span>403</span>
        <h1>This area is private.</h1>
        <p>You do not have permission to access this page.</p>
        <a href="{{ route('portfolio.index') }}">Return to portfolio ↗</a>
    </main>
</body>
</html>
