<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CebuQueue</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f6f5; color: #1f2937; display: flex; min-height: 100vh; }
        .side { width: 230px; background: #0f5c4d; color: #fff; padding: 24px 16px; display: flex; flex-direction: column; }
        .side h1 { font-size: 22px; margin: 0 0 24px 8px; }
        .role-label { margin: 16px 8px 12px; color: #a7f3d0; font-size: 13px; }
        .side a { display: block; color: #d1fae5; text-decoration: none; padding: 11px 12px; border-radius: 8px; margin-bottom: 4px; font-size: 15px; }
        .side a:hover, .side a.active { background: #0f766e; color: #fff; }
        .side form { margin-top: auto; }
        .side button { width: 100%; background: transparent; color: #d1fae5; border: 1px solid #34d399; padding: 10px; border-radius: 8px; cursor: pointer; font-size: 14px; }
        .side button:hover { background: #0f766e; }
        .main { flex: 1; padding: 28px 32px; }
        .top h2 { margin: 0; font-size: 24px; }
        .top p { margin: 4px 0 24px; color: #6b7280; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat { background: #fff; padding: 20px; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .stat .num { font-size: 34px; font-weight: bold; color: #0f5c4d; }
        .stat .lbl { color: #6b7280; font-size: 14px; margin-top: 4px; }
        h3 { margin: 0 0 12px; font-size: 18px; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .actions a { background: #0f766e; color: #fff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: bold; font-size: 15px; }
        .actions a:hover { background: #0d5f58; }
        .actions a.alt { background: #fff; color: #0f5c4d; border: 1px solid #0f766e; }
        @media (max-width: 700px) { body { flex-direction: column; } .side { width: 100%; } }
    </style>
</head>
<body>
    <div class="side">
        <h1>CebuQueue</h1>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('queue') }}" class="{{ request()->routeIs('queue') ? 'active' : '' }}">Queue</a>
        <a href="{{ route('patients') }}" class="{{ request()->routeIs('patients') ? 'active' : '' }}">Patients</a>
        <a href="{{ route('now-serving') }}" class="{{ request()->routeIs('now-serving') ? 'active' : '' }}">Now serving board</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('appointments') }}" class="{{ request()->routeIs('appointments') ? 'active' : '' }}">Appointments</a>
            <a href="{{ route('services-staff') }}" class="{{ request()->routeIs('services-staff') ? 'active' : '' }}">Services &amp; staff</a>
        @endif
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">Profile</a>
        <p class="role-label">{{ auth()->user()->isAdmin() ? 'Health Center Admin' : 'Staff' }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </div>
    <div class="main">
        @yield('content')
    </div>
</body>
</html>