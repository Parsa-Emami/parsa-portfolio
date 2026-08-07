<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parsa Emami - Portfolio</title>
    @vite(['resources/css/portfolio/global.scss', 'resources/js/portfolio/app.js'])
</head>

{{-- اضافه کردن کلاس is-loading به صورت پیش‌فرض --}}
<body class="is-loading">
    
    {{-- ساختار پرلودر در بالاترین نقطه بادی --}}
    <div class="c-preloader">
        <div class="c-preloader__bottom">
            <span class="c-preloader__name">Parsa Emami © 2024</span>
            <span class="c-preloader__counter">0%</span>
        </div>
    </div>

    <main class="main-content">
        @yield('content')
    </main>

    <div class="c-cursor"></div>
</body>
</html>