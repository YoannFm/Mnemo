<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - Mnémo Admin' : 'Mnémo Admin' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #EFB702;
            --accent-hover: #d6a502;
            --accent-light: rgba(239,183,2,.12);
            --text-primary: #DDDDDD;
            --text-muted: #9ca3af;
            --card-bg: #212227;
            --card-border: #2E2E34;
            --body-bg: #111113;
            --sidebar-bg: #1a1b1f;
            --success-color: #22c55e;
            --danger-color: #ff5956;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Rubik', sans-serif;
            background: var(--body-bg);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        ::selection { background: var(--accent); color: #212227; }
        ::-webkit-scrollbar { width: 7px; }
        ::-webkit-scrollbar-track { background: var(--card-bg); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        /* ── Sidebar ── */
        #admin-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: transform .3s ease;
        }

        #admin-sidebar .sidebar-logo {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid var(--card-border);
        }

        #admin-sidebar .sidebar-logo a {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: none;
        }

        #admin-sidebar .sidebar-logo a span { color: var(--text-primary); }

        #admin-sidebar .sidebar-logo small {
            display: block;
            font-size: .65rem;
            color: var(--text-muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        #admin-sidebar nav { flex: 1; padding: 1rem 0; overflow-y: auto; }

        #admin-sidebar .nav-label {
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: .75rem 1.25rem .35rem;
        }

        #admin-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .65rem 1.25rem;
            color: var(--text-muted);
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: color .2s, background .2s, border-color .2s;
        }

        #admin-sidebar .nav-link:hover {
            color: var(--text-primary);
            background: rgba(239,183,2,.06);
        }

        #admin-sidebar .nav-link.active {
            color: var(--accent);
            border-left-color: var(--accent);
            background: rgba(239,183,2,.1);
        }

        #admin-sidebar .nav-link i { font-size: 1rem; width: 1.1rem; text-align: center; }

        #admin-sidebar .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--card-border);
        }

        #admin-sidebar .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: var(--text-muted);
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            text-decoration: none;
            transition: color .2s;
        }

        #admin-sidebar .sidebar-footer a:hover { color: var(--accent); }

        /* ── Main area ── */
        #admin-main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        #admin-topbar {
            background: var(--sidebar-bg);
            border-bottom: 1px solid var(--card-border);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        #admin-topbar .topbar-title {
            font-weight: 700;
            font-size: .9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-primary);
            flex: 1;
        }

        #admin-topbar .topbar-user {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        #admin-topbar .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .75rem;
            color: #212227;
        }

        /* ── Page content ── */
        #admin-content {
            flex: 1;
            padding: 2rem 1.5rem;
        }

        /* ── Cards ── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 4px;
            color: var(--text-primary);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: .875rem 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-size: .85rem;
        }

        /* ── Stat cards ── */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-top: 3px solid var(--accent);
            border-radius: 4px;
            padding: 1.25rem;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-top: .25rem;
        }

        .stat-card i {
            font-size: 1.75rem;
            color: rgba(239,183,2,.3);
        }

        /* ── Buttons ── */
        .btn {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-radius: 0;
            font-size: .8rem;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #212227;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            color: #212227;
        }

        .btn-outline-primary {
            color: var(--accent);
            border-color: var(--accent);
        }

        .btn-outline-primary:hover {
            background: var(--accent);
            color: #212227;
        }

        .btn-danger {
            background: #ff5956;
            border-color: #ff5956;
            color: #fff;
        }

        .btn-danger:hover {
            background: #e63e3b;
            border-color: #e63e3b;
        }

        .btn-sm { font-size: .7rem; padding: .3rem .6rem; }

        /* ── Tables ── */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--card-border);
            color: var(--text-primary);
        }

        .table > :not(caption) > * > * {
            background-color: transparent;
            color: var(--text-primary);
            border-bottom-color: var(--card-border);
        }

        .table thead th {
            background: var(--accent) !important;
            color: #212227 !important;
            border-color: var(--accent) !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-size: .75rem;
        }

        .table tbody { background: var(--card-bg); }

        .table tbody td {
            background: var(--card-bg);
            padding: 10px 16px;
            vertical-align: middle;
        }

        /* ── Forms ── */
        .form-control, .form-select {
            background: var(--body-bg) !important;
            border: 2px solid var(--card-border) !important;
            color: var(--text-primary) !important;
            border-radius: 0;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px var(--accent-light) !important;
        }

        .form-control::placeholder { color: var(--text-muted); }

        .form-label {
            font-weight: 600;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
        }

        /* ── Alerts ── */
        .alert-success {
            background: rgba(34,197,94,.1);
            border-color: rgba(34,197,94,.3);
            color: var(--success-color);
        }

        .alert-danger {
            background: rgba(239,68,68,.1);
            border-color: rgba(239,68,68,.3);
            color: var(--danger-color);
        }

        /* ── Badges ── */
        .badge-admin {
            background: rgba(239,183,2,.15);
            color: var(--accent);
            font-size: .65rem;
            padding: .2em .55em;
            border-radius: 3px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Hamburger ── */
        #sidebar-toggle {
            display: none;
            background: none;
            border: 1px solid rgba(239,183,2,.4);
            color: var(--accent);
            padding: .3rem .55rem;
            cursor: pointer;
            border-radius: 0;
        }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            #admin-sidebar {
                transform: translateX(-100%);
            }

            #admin-sidebar.open {
                transform: translateX(0);
            }

            #admin-main {
                margin-left: 0;
            }

            #sidebar-toggle {
                display: flex;
                align-items: center;
            }

            #sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 199;
            }

            #sidebar-overlay.open {
                display: block;
            }
        }
    </style>
</head>
<body>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebar-overlay"></div>

{{-- ─── Sidebar ──────────────────────────────────────── --}}
<aside id="admin-sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('admin.dashboard') }}">Mn<span>émo</span></a>
        <small>Administration</small>
    </div>

    <nav>
        <div class="nav-label">Menu</div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Utilisateurs
        </a>

        <a href="{{ route('admin.modules.index') }}"
           class="nav-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
            <i class="bi bi-collection"></i> Modules publics
        </a>

        <a href="{{ route('admin.navbar.index') }}"
           class="nav-link {{ request()->routeIs('admin.navbar.*') ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i> Navigation
        </a>

        <a href="{{ route('admin.settings.index') }}"
           class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> Paramètres
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('dashboard') }}">
            <i class="bi bi-arrow-left-circle"></i> Retour au site
        </a>
    </div>
</aside>

{{-- ─── Main ─────────────────────────────────────────── --}}
<div id="admin-main">

    {{-- Topbar --}}
    <div id="admin-topbar">
        <button id="sidebar-toggle" type="button" aria-label="Menu">
            <i class="bi bi-list" style="font-size:1.2rem;"></i>
        </button>

        <div class="topbar-title">
            {{ $pageTitle ?? 'Administration' }}
        </div>

        <div class="topbar-user">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}" class="ms-2">
                @csrf
                <button type="submit" style="background:none;border:none;color:#ff5956;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;cursor:pointer;padding:0;" title="Déconnexion">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Déco.</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Flash messages --}}
    <div class="px-4 pt-3">
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 py-2">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div id="admin-content">
        {{ $slot }}
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    }

    if (toggle) toggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
</script>

</body>
</html>
