<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parsa Emami - Portfolio</title>
    
    <!-- لود کردن فایل‌های کامپایل‌شده توسط Vite -->
    @vite(['resources/css/portfolio/global.scss', 'resources/js/portfolio/app.js'])
</head>
<body>
    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>