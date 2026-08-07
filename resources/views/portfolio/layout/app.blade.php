<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parsa Emami - Portfolio</title>
    @vite(['resources/css/portfolio/global.scss', 'resources/js/portfolio/app.js'])
</head>

{{-- data-barba="wrapper" کل سایت را در بر می‌گیرد --}}
<body class="is-loading" data-barba="wrapper">
    
    {{-- پرلودر سایت (فاز ۶) --}}
    <div class="c-preloader">
        <div class="c-preloader__bottom">
            <span class="c-preloader__name">Parsa Emami © 2024</span>
            <span class="c-preloader__counter">0%</span>
        </div>
    </div>

    {{-- پرده ترانزیشن صفحات (فاز ۷) --}}
    <div class="c-transition-overlay"></div>

    {{-- data-barba="container" فقط محتوای این بخش با رفتن به صفحه دیگر عوض می‌شود --}}
    <main class="main-content" data-barba="container" data-barba-namespace="home">
        @yield('content')
    </main>

    {{-- کرسر کاستوم --}}
    <div class="c-cursor"></div>
</body>
</html>