<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>ورود مدیریت پورتفولیو</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body admin-login-body">
    <main class="admin-login-card">
        <a class="admin-login-brand" href="{{ route('portfolio.index') }}"><span>PE</span> Parsa Portfolio</a>
        <p>ورود امن به پنل مدیریت</p>

        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form">
            @csrf
            <label>
                <span>ایمیل</span>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email') <small>{{ $message }}</small> @enderror
            </label>
            <label>
                <span>رمز عبور</span>
                <input type="password" name="password" autocomplete="current-password" required>
                @error('password') <small>{{ $message }}</small> @enderror
            </label>
            <label class="admin-checkbox">
                <input type="checkbox" name="remember" value="1">
                <span>مرا به خاطر بسپار</span>
            </label>
            <button class="admin-primary-button" type="submit">ورود به پنل</button>
        </form>
    </main>
</body>
</html>
