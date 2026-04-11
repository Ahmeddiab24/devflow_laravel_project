<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DevFlow') — DevOps Practice App</title>

    <!-- Google Fonts: Syne (display) + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:        #0d0f14;
            --surface:   #161920;
            --surface2:  #1e2230;
            --border:    #2a2f3e;
            --accent:    #6c63ff;
            --accent2:   #00d4aa;
            --danger:    #ff4d6d;
            --warning:   #f5a623;
            --success:   #2ecc71;
            --text:      #e8eaf0;
            --muted:     #7a8099;
            --font:      'Inter', sans-serif;
            --mono:      'JetBrains Mono', monospace;
            --display:   'Syne', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; background: var(--bg); color: var(--text); font-family: var(--font); font-size: 14px; line-height: 1.6; }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        /* ── LAYOUT ── */
        .app-layout { display: flex; height: 100vh; overflow: hidden; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 240px;
            min-width: 240px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 0;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }

        .sidebar-logo-text {
            font-family: var(--display);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--text);
        }

        .sidebar-section { padding: 12px 12px 4px; }
        .sidebar-section-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
            padding: 0 12px;
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.15s;
        }
        .nav-link:hover { background: var(--surface2); color: var(--text); }
        .nav-link.active { background: rgba(108,99,255,0.15); color: var(--accent); }
        .nav-link .icon { font-size: 16px; width: 20px; text-align: center; }

        .sidebar-footer {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid var(--border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            background: var(--surface2);
        }

        .avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--muted); }

        /* ── MAIN CONTENT ── */
        .main { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .page-title { font-family: var(--display); font-size: 20px; font-weight: 700; }
        .page-subtitle { font-size: 12px; color: var(--muted); margin-top: 1px; }

        .content { padding: 28px; flex: 1; }

        /* ── CARDS ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }

        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .card-title { font-family: var(--display); font-size: 15px; font-weight: 700; }

        /* ── STATS GRID ── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--accent);
        }
        .stat-card.green::before { background: var(--success); }
        .stat-card.orange::before { background: var(--warning); }
        .stat-card.red::before { background: var(--danger); }

        .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 8px; }
        .stat-value { font-family: var(--display); font-size: 32px; font-weight: 800; line-height: 1; }
        .stat-sub { font-size: 11px; color: var(--muted); margin-top: 6px; }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #5a54e0; }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--surface2); color: var(--text); }
        .btn-danger { background: rgba(255,77,109,0.15); color: var(--danger); border: 1px solid rgba(255,77,109,0.3); }
        .btn-danger:hover { background: var(--danger); color: white; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-gray    { background: rgba(122,128,153,0.15); color: var(--muted); }
        .badge-blue    { background: rgba(59,130,246,0.15);  color: #60a5fa; }
        .badge-yellow  { background: rgba(245,166,35,0.15);  color: var(--warning); }
        .badge-green   { background: rgba(46,204,113,0.15);  color: var(--success); }
        .badge-red     { background: rgba(255,77,109,0.15);  color: var(--danger); }
        .badge-purple  { background: rgba(108,99,255,0.15);  color: var(--accent); }

        /* ── TABLE ── */
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); padding: 10px 16px; border-bottom: 1px solid var(--border); }
        .table td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13.5px; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: rgba(255,255,255,0.02); }

        /* ── FORMS ── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 12px;
            color: var(--text);
            font-size: 14px;
            font-family: var(--font);
            transition: border-color 0.15s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); }
        .form-control::placeholder { color: var(--muted); }
        select.form-control { cursor: pointer; }
        textarea.form-control { resize: vertical; min-height: 80px; }

        .form-error { font-size: 12px; color: var(--danger); margin-top: 4px; }

        /* ── ALERTS ── */
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: rgba(46,204,113,0.1); border: 1px solid rgba(46,204,113,0.3); color: var(--success); }
        .alert-danger  { background: rgba(255,77,109,0.1);  border: 1px solid rgba(255,77,109,0.3);  color: var(--danger); }

        /* ── PROGRESS BAR ── */
        .progress { background: var(--surface2); border-radius: 4px; height: 6px; overflow: hidden; }
        .progress-bar { height: 100%; background: var(--accent); border-radius: 4px; transition: width 0.3s; }
        .progress-bar.green  { background: var(--success); }
        .progress-bar.yellow { background: var(--warning); }

        /* ── GRID LAYOUTS ── */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-auto { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

        /* ── KANBAN ── */
        .kanban { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; min-height: 400px; }
        .kanban-col { background: var(--surface2); border-radius: 10px; padding: 12px; }
        .kanban-col-header { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
        .kanban-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: border-color 0.15s, transform 0.1s;
        }
        .kanban-card:hover { border-color: var(--accent); transform: translateY(-1px); }
        .kanban-card-title { font-size: 13px; font-weight: 600; margin-bottom: 8px; }
        .kanban-card-meta { display: flex; align-items: center; justify-content: space-between; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .kanban { grid-template-columns: 1fr; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-layout">

    <!-- ── SIDEBAR ─────────────────────────────────────────────────────────── -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">⚡</div>
            <span class="sidebar-logo-text">DevFlow</span>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">🏠</span> Dashboard
            </a>
            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <span class="icon">📁</span> Projects
            </a>
            <a href="{{ route('team.index') }}" class="nav-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
                <span class="icon">👥</span> Team
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">DevOps Tools</div>
            <a href="http://localhost:9090" target="_blank" class="nav-link">
                <span class="icon">📊</span> Prometheus
            </a>
            <a href="http://localhost:3000" target="_blank" class="nav-link">
                <span class="icon">📈</span> Grafana
            </a>
            <a href="http://localhost:8025" target="_blank" class="nav-link">
                <span class="icon">📬</span> Mailpit
            </a>
            <a href="{{ route('health') }}" target="_blank" class="nav-link">
                <span class="icon">💚</span> Health Check
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Practice</div>
            <a href="#" class="nav-link" onclick="showDevOpsPanel(event)">
                <span class="icon">🐳</span> Docker Info
            </a>
            <a href="#" class="nav-link" onclick="triggerJob(event)">
                <span class="icon">⚙️</span> Trigger Job
            </a>
            <a href="#" class="nav-link" onclick="flushCache(event)">
                <span class="icon">🗑️</span> Flush Cache
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px;" title="Logout">↩</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ── MAIN ─────────────────────────────────────────────────────────────── -->
    <main class="main">
        <div class="topbar">
            <div>
                <div class="page-title">@yield('page-title', 'Dashboard')</div>
                <div class="page-subtitle">@yield('page-subtitle', '')</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                @yield('topbar-actions')
            </div>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">❌ {{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
function triggerJob(e) {
    e.preventDefault();
    fetch('/api/v1/dashboard/stats', {
        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
    });
    alert('✅ Job dispatched to Redis queue!\nCheck: docker compose logs -f worker');
}

function flushCache(e) {
    e.preventDefault();
    if (confirm('Flush all Redis cache? This will clear dashboard stats cache.')) {
        alert('Run: docker compose exec app php artisan cache:clear');
    }
}

function showDevOpsPanel(e) {
    e.preventDefault();
    alert(
        '🐳 DevOps Commands:\n\n' +
        '▸ docker compose up -d\n' +
        '▸ docker compose logs -f worker\n' +
        '▸ docker compose exec app php artisan migrate\n' +
        '▸ docker compose exec app php artisan queue:work\n' +
        '▸ docker compose exec app php artisan tinker\n' +
        '▸ docker compose ps\n\n' +
        '📊 Monitoring:\n' +
        '▸ Prometheus: http://localhost:9090\n' +
        '▸ Grafana:    http://localhost:3000\n' +
        '▸ Mailpit:    http://localhost:8025'
    );
}
</script>

@stack('scripts')
</body>
</html>
