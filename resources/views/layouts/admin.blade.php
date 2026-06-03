<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' — Admin' : 'Administration — Mnémo' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e2130;
            --sidebar-border: #2a2e45;
            --topbar-bg: #ffffff;
            --body-bg: #f5f7fb;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --text-sidebar: #9ba1b4;
            --card-bg: #ffffff;
            --card-border: #e9ecef;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--body-bg);
            color: #495057;
            min-height: 100vh;
            margin: 0;
        }

        a { text-decoration: none; }

        .wrapper { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 200;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--sidebar-border);
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
        }

        .sidebar-brand .brand-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; color: #fff; font-weight: 800; flex-shrink: 0;
        }

        .sidebar-brand .back-link {
            font-size: .7rem;
            color: var(--text-sidebar);
            margin-top: .1rem;
            display: block;
        }

        .sidebar-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }

        .sidebar-header {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-sidebar);
            padding: 1rem 1.5rem .35rem;
            opacity: .6;
            list-style: none;
        }

        .sidebar-item { list-style: none; }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1.5rem;
            color: var(--text-sidebar);
            font-size: .85rem;
            font-weight: 500;
            transition: all .2s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover { color: #fff; background: rgba(255,255,255,.05); }

        .sidebar-link.active {
            color: #fff;
            background: rgba(59,130,246,.15);
            border-left-color: var(--accent);
        }

        .sidebar-link i { font-size: 1rem; width: 20px; text-align: center; }

        /* ── Main ── */
        .main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--card-border);
            padding: 0 1.5rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0; z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        #sidebar-toggle {
            background: none; border: none;
            font-size: 1.3rem; color: #495057;
            cursor: pointer; display: none; padding: .25rem;
        }

        .topbar-title { font-weight: 700; font-size: .95rem; color: #212529; }

        .topbar-user {
            display: flex; align-items: center; gap: .5rem;
            font-size: .85rem; font-weight: 600; color: #495057;
        }

        .topbar-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--accent); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .8rem;
        }

        .topbar-btn {
            background: none;
            border: 1px solid var(--card-border);
            border-radius: 6px;
            padding: .35rem .75rem;
            font-size: .8rem; font-weight: 600;
            color: #495057; cursor: pointer;
            display: flex; align-items: center; gap: .4rem;
            transition: all .2s;
        }

        .topbar-btn:hover { background: var(--body-bg); }

        /* ── Content ── */
        .content { padding: 2rem 1.5rem; flex: 1; }

        /* ── Cards ── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.25rem;
            font-weight: 700; font-size: .9rem;
        }

        /* ── Stat cards ── */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }

        .stat-value { font-size: 2rem; font-weight: 800; color: #212529; line-height: 1; }
        .stat-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: #6c757d; margin-top: .35rem; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        /* ── Tables ── */
        .table thead th {
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .8px;
            color: #6c757d; background: #f8f9fa;
            border-bottom: 2px solid var(--card-border);
        }

        /* ── Footer ── */
        .admin-footer {
            padding: 1rem 1.5rem; text-align: center;
            font-size: .78rem; color: #6c757d;
            border-top: 1px solid var(--card-border);
            background: var(--card-bg);
        }

        /* ── Overlay mobile ── */
        #sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 199;
        }

        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            #sidebar-toggle { display: block; }
            #sidebar-overlay.open { display: block; }
        }
    </style>
</head>
<body>

<div class="wrapper">

    {{-- ── Sidebar ── --}}
    <nav id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-speedometer2"></i></div>
            <div>
                <div>Mnémo Admin</div>
                <a href="{{ route('dashboard') }}" class="back-link">
                    <i class="bi bi-arrow-left me-1"></i>Retour au site
                </a>
            </div>
        </div>

        <ul class="sidebar-nav list-unstyled">

            <li class="sidebar-header">Général</li>

            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            </li>

            <li class="sidebar-header">Contenu</li>

            <li class="sidebar-item">
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Utilisateurs
                </a>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('admin.modules.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i> Modules publics
                </a>
            </li>

            <li class="sidebar-header">Paramètres</li>

            <li class="sidebar-item">
                <a href="{{ route('admin.navbar.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.navbar.*') ? 'active' : '' }}">
                    <i class="bi bi-list-ul"></i> Navigation
                </a>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('admin.settings.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
            </li>

        </ul>
    </nav>

    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    {{-- ── Main ── --}}
    <div class="main">

        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <span class="topbar-title">{{ $pageTitle ?? 'Administration' }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @auth
                <div class="topbar-user">
                    <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    {{ Auth::user()->name }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="topbar-btn">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </button>
                </form>
                @endauth
            </div>
        </div>

        <div class="px-4 pt-3">
            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 py-2">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="content">
            {{ $slot }}
        </div>

        <div class="admin-footer">Mnémo Administration</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebar-overlay').classList.toggle('open');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('open');
    }
</script>
</body>
</html>
