<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'مدیریت پورتفولیو')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span>PE</span><strong>مدیریت پورتفولیو</strong></a>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" @class(['is-active'=>request()->routeIs('admin.dashboard')])>داشبورد</a>
            <a href="{{ route('admin.projects.index') }}" @class(['is-active'=>request()->routeIs('admin.projects.*')])>پروژه‌ها</a>
            <a href="{{ route('admin.skills.index') }}" @class(['is-active'=>request()->routeIs('admin.skills.*')])>مهارت‌ها</a>
            <a href="{{ route('admin.experiences.index') }}" @class(['is-active'=>request()->routeIs('admin.experiences.*')])>سوابق</a>
            <a href="{{ route('admin.messages.index') }}" @class(['is-active'=>request()->routeIs('admin.messages.*')])>پیام‌ها</a>
            <a href="{{ route('admin.settings.edit') }}" @class(['is-active'=>request()->routeIs('admin.settings.*')])>تنظیمات سایت</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="{{ route('portfolio.index') }}" target="_blank">مشاهده سایت ↗</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit">خروج</button></form>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-menu-button" data-admin-menu>☰</button>
            <div><p>@yield('eyebrow','Portfolio CMS')</p><h1>@yield('heading','داشبورد')</h1></div>
            <div class="admin-user">{{ auth()->user()->name }}</div>
        </header>
        @if(session('success'))<div class="admin-flash">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="admin-error-summary"><strong>عملیات انجام نشد.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
