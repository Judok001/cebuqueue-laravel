<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CebuQueue') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f3f6f5; color: #1f2937; font-family: Arial, sans-serif; }
        .header { display: flex; justify-content: space-between; align-items: center; gap: 16px; width: min(100% - 32px, 1080px); margin: 0 auto; padding: 22px 0; }
        .brand { color: #0f5c4d; font-size: 22px; font-weight: 700; text-decoration: none; }
        .header nav { display: flex; align-items: center; gap: 10px; }
        .header nav a { padding: 10px 14px; border-radius: 8px; color: #0f5c4d; text-decoration: none; font-weight: 600; }
        .header nav a.active, .header nav a:hover { background: #0f766e; color: #fff; }
        main { display: grid; place-items: start center; padding: 48px 16px; }
        .card { width: min(100%, 440px); padding: 32px; border-radius: 16px; background: #fff; box-shadow: 0 8px 28px rgba(15, 92, 77, .1); }
        .card h1 { margin: 0 0 8px; color: #0f5c4d; font-size: 26px; }
        .intro { margin: 0 0 24px; color: #6b7280; line-height: 1.5; }
        form { display: grid; gap: 10px; }
        label { margin-top: 8px; font-size: 14px; font-weight: 600; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #cbd5d1; border-radius: 8px; font: inherit; }
        input:focus { outline: 2px solid #0f766e; outline-offset: 1px; }
        .remember { display: flex; align-items: center; gap: 8px; margin: 8px 0; font-size: 14px; }
        button { width: 100%; margin-top: 10px; padding: 12px; border: 0; border-radius: 8px; background: #0f766e; color: #fff; cursor: pointer; font: inherit; font-weight: 700; }
        button:hover { background: #0d5f58; }
        .error { color: #b91c1c; font-size: 14px; }
        .link { margin: 20px 0 0; text-align: center; color: #4b5563; font-size: 14px; }
        .link a, .forgot-link { color: #0f766e; font-weight: 600; }
        .forgot-link { justify-self: end; font-size: 14px; }
        @media (max-width: 520px) {
            .header { align-items: flex-start; }
            .header nav { gap: 2px; }
            .header nav a { padding: 9px; font-size: 14px; }
            main { padding-top: 24px; }
            .card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a class="brand" href="{{ route('login') }}">CebuQueue</a>
        <nav aria-label="Account navigation">
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Log in</a>
        </nav>
    </header>
    <main>
        <section class="card">
            @yield('content')
        </section>
    </main>
</body>
</html>