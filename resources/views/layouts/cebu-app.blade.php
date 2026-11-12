
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CebuQueue')</title>
    @yield('head')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f6f5; color: #1f2937; display: flex; min-height: 100vh; }
        .side { flex: 0 0 230px; width: 230px; background: #0f5c4d; color: #fff; padding: 24px 16px; display: flex; flex-direction: column; }
        .side h1 { font-size: 22px; margin: 0 0 24px 8px; }
        .role-label { margin: 16px 8px 12px; color: #a7f3d0; font-size: 13px; }
        .nav-links a { display: block; color: #d1fae5; text-decoration: none; padding: 11px 12px; border-radius: 8px; margin-bottom: 4px; font-size: 15px; }
        .nav-links a:hover, .nav-links a.active { background: #0f766e; color: #fff; }
        .side form { margin-top: auto; }
        .side button { width: 100%; background: transparent; color: #d1fae5; border: 1px solid #34d399; padding: 10px; border-radius: 8px; cursor: pointer; font-size: 14px; }
        .side button:hover, .side button:focus-visible { background: #0f766e; }
        .main { flex: 1; min-width: 0; padding: 28px 32px; }
        .top h2 { margin: 0; font-size: 24px; }
        .top p { margin: 4px 0 24px; color: #6b7280; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat { background: #fff; padding: 20px; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .stat .num { font-size: 34px; font-weight: bold; color: #0f5c4d; }
        .stat .lbl { color: #6b7280; font-size: 14px; margin-top: 4px; }
        .form-card { max-width: 560px; margin-bottom: 24px; }
        .form-card form { display: grid; gap: 10px; }
        .form-card label { margin-top: 6px; font-weight: 600; }
        .form-card input { width: 100%; padding: 11px; border: 1px solid #cbd5d1; border-radius: 8px; font: inherit; }
        .form-card button { padding: 11px 16px; border: 0; border-radius: 8px; background: #0f766e; color: #fff; cursor: pointer; font: inherit; font-weight: 700; }
        .form-card button:hover { background: #0d5f58; }
        .error { color: #b91c1c; font-size: 14px; }
        .notice { margin-bottom: 16px; color: #0f5c4d; }
        h3 { margin: 0 0 12px; font-size: 18px; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .actions a { background: #0f766e; color: #fff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: bold; font-size: 15px; }
        .actions a:hover { background: #0d5f58; }
        .actions a.alt { background: #fff; color: #0f5c4d; border: 1px solid #0f766e; }
        .stat input, .stat select { width: 100%; padding: 11px; border: 1px solid #cbd5d1; border-radius: 8px; font: inherit; }
        .stat button { padding: 11px 16px; border: 0; border-radius: 8px; background: #0f766e; color: #fff; cursor: pointer; font: inherit; font-weight: 700; }
        .stat button:hover { background: #0d5f58; }
        .stat button.button-danger { background: #b91c1c; }
        .stat button.button-danger:hover { background: #991b1b; }
        .section-heading { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; }
        .section-heading h3 { margin: 0; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { color: #4b5563; font-size: 13px; }
        .search-form, .report-filter { display: flex; align-items: end; flex-wrap: wrap; gap: 10px; }
        .search-form input { min-width: 220px; }
        .patient-queue-form { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; min-width: 260px; }
        .patient-queue-form select { flex: 1 1 140px; min-width: 140px; }
        .patient-queue-form button { white-space: nowrap; }
        .report-filter label { margin: 0; }
        .search-form button, .report-filter button { white-space: nowrap; }
        .queue-services, .service-list { display: grid; gap: 16px; }
        .dashboard-queue-heading { margin-top: 28px; }
        .dashboard-queue-heading p { margin: 4px 0 0; }
        .serving-card { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 16px; margin-bottom: 16px; border-radius: 10px; background: #ecfdf5; }
        .serving-card strong, .serving-card .lbl { display: block; }
        .serving-card .actions { gap: 8px; }
        .serving-card form { margin: 0; }
        .button-secondary { background: #6b7280 !important; }
        .service-row { display: grid; gap: 14px; padding: 16px 0; border-top: 1px solid #e5e7eb; }
        .service-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); align-items: end; gap: 12px; }
        .service-fields label { display: block; margin: 0 0 6px; }
        .service-fields .checkbox-label { display: flex; align-items: center; gap: 8px; }
        .service-fields .checkbox-label input { width: auto; }
        .service-actions { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
        @media (max-width: 700px) {
            body { flex-direction: column; }
            .side { flex: 0 0 auto; width: 100%; padding: 16px; }
            .side h1 { margin: 0 0 12px; }
            .nav-links { display: flex; flex-wrap: wrap; gap: 4px; }
            .nav-links a { margin: 0; }
            .side form { margin-top: 12px; }
            .main { padding: 24px 16px; }
            .actions a { flex: 1 1 200px; text-align: center; }
        }
        @media (max-width: 420px) { .stats { grid-template-columns: 1fr; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/cebuqueue-ui.css') }}">
</head>
<body>
    <div class="side">
        <h1 class="brand-mark">Cebu<span>Queue</span></h1>
        <nav class="nav-links" aria-label="Main navigation">
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
        </nav>
        <div class="sidebar-account">
            <span class="account-indicator"></span>
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <p class="role-label">{{ auth()->user()->isAdmin() ? 'Health Center Admin' : 'Clinic Staff' }}</p>
            </div>
        </div>
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