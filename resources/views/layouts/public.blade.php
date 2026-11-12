<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CebuQueue')</title>
    @yield('head')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f3f6f5; color: #1f2937; font-family: Arial, sans-serif; }
        header { display: flex; justify-content: space-between; align-items: center; gap: 16px; max-width: 1080px; margin: auto; padding: 22px 16px; }
        header a { color: #0f5c4d; text-decoration: none; font-weight: 700; }
        .brand { font-size: 22px; }
        nav { display: flex; flex-wrap: wrap; gap: 12px; }
        main { width: min(100% - 32px, 1080px); margin: 20px auto 48px; }
        .card { padding: 24px; border-radius: 14px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .page-title { margin: 0 0 8px; color: #0f5c4d; }
        .muted { color: #6b7280; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        form { display: grid; gap: 10px; }
        label { margin-top: 6px; font-weight: 600; }
        input, select { width: 100%; padding: 11px; border: 1px solid #cbd5d1; border-radius: 8px; font: inherit; }
        button, .button { display: inline-block; padding: 11px 16px; border: 0; border-radius: 8px; background: #0f766e; color: #fff; cursor: pointer; font: inherit; font-weight: 700; text-decoration: none; }
        .error { color: #b91c1c; font-size: 14px; }
        .ticket-code { color: #0f5c4d; font-size: clamp(2.5rem, 8vw, 4rem); font-weight: 800; letter-spacing: .04em; }
        .ticket-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        @media print { header, .ticket-actions { display: none; } body { background: #fff; } main { width: 100%; margin: 0; } .card { box-shadow: none; } }
        @media (max-width: 560px) { header { align-items: flex-start; flex-direction: column; } main { margin-top: 8px; } .card { padding: 20px; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/cebuqueue-ui.css') }}">
</head>
<body class="public-layout">
    <header>
        <a class="brand" href="{{ route('home') }}">CebuQueue</a>
        <nav aria-label="Public navigation">
            <a href="{{ route('reserve.create') }}">Get a queue ticket</a>
            <a href="{{ route('now-serving') }}">Now serving</a>
            <a href="{{ route('login') }}">Staff login</a>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
