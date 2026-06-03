<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo - Apprenez par la répétition espacée' }}</title>

    <meta name="description" content="{{ isset($metaDescription) ? $metaDescription : 'Mnémo est une application de mémorisation par répétition espacée. Créez vos modules, apprenez avec le mode Anki ou testez vos connaissances.' }}">
    <meta name="keywords" content="mémorisation, répétition espacée, anki, flashcard, apprentissage, quiz, mnémo">
    <meta name="author" content="YoannFM">
    <meta name="robots" content="index, follow">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo - Apprenez par la répétition espacée' }}">
    <meta property="og:description" content="Application de mémorisation par répétition espacée. Créez des modules, entraînez-vous en mode Anki et suivez votre progression.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Mnémo">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo' }}">
    <meta name="twitter:description" content="Application de mémorisation par répétition espacée.">

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
            --header-bg: #1a1b1f;
            --success-color: #22c55e;
            --danger-color: #ff5956;
        }

        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        ::selection { background: var(--accent); color: #212227; }
        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: var(--card-bg); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Rubik', sans-serif;
            font-weight: 700;
        }

        /* ── Header ── */
        #main-header {
            background: var(--header-bg);
            border-bottom: 1px solid var(--card-border);
            position: sticky;
            top: 0;
            z-index: 100;
            height: 56px;
        }

        #main-header .navbar {
            height: 56px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        #main-header .navbar-brand {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: 1px;
            text-decoration: none;
            text-transform: uppercase;
        }

        #main-header .navbar-brand span {
            color: var(--text-primary);
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: .35rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 1rem;
            height: 56px;
            border-top: 2px solid transparent;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
            white-space: nowrap;
        }

        .nav-link-custom:hover {
            color: var(--text-primary);
            border-top-color: rgba(239,183,2,.4);
        }

        .nav-link-custom.active {
            color: var(--accent);
            border-top-color: var(--accent);
        }

        .nav-link-custom i { font-size: .85rem; }

        /* Avatar utilisateur */
        .user-avatar {
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
            flex-shrink: 0;
        }

        /* Dropdown utilisateur */
        .dropdown-menu-dark-custom {
            background: var(--card-bg);
            border: 1px solid rgba(239,183,2,.3);
            border-radius: 4px;
            padding: .25rem 0;
            min-width: 180px;
            margin-top: 8px !important;
        }

        .dropdown-menu-dark-custom .dropdown-item {
            color: var(--text-muted);
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            padding: .5rem 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .dropdown-menu-dark-custom .dropdown-item:hover {
            background: var(--accent-light);
            color: var(--text-primary);
        }

        .dropdown-menu-dark-custom .dropdown-divider {
            border-color: var(--card-border);
        }

        /* ── Contenu ── */
        #main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        #main-content {
            background: #2E2E34;
        }

        .page-content {
            padding: 2rem 1.5rem;
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Cards ── */
        .card {
            background: var(--card-bg);
            border: 2px solid var(--accent);
            border-radius: 5px;
            color: var(--text-primary);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.25rem;
        }

        /* ── Boutons ── */
        .btn {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-radius: 0;
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
            border-color: var(--accent);
            color: #212227;
        }

        .btn-danger {
            background: #ff5956;
            border-color: #ff5956;
            color: #DDDDDD;
        }

        .btn-danger:hover {
            background: #ff403d;
            border-color: #ff403d;
            color: #DDDDDD;
        }

        @media (max-width: 768px) { .btn { min-height: 44px; } }

        /* ── Formulaires ── */
        .form-control, .form-select {
            background: var(--body-bg) !important;
            border: 2px solid var(--accent) !important;
            color: var(--text-primary) !important;
            border-radius: 0;
            outline: 0 !important;
        }

        .form-control:focus, .form-select:focus {
            background: var(--body-bg) !important;
            border-color: var(--accent) !important;
            color: var(--text-primary) !important;
            box-shadow: 0 0 0 3px var(--accent-light) !important;
        }

        .form-control::placeholder { color: var(--text-muted); }

        .form-label {
            font-weight: 600;
            font-size: .875rem;
            color: var(--text-muted);
            margin-bottom: .4rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Badges ── */
        .badge-public {
            background: rgba(34,197,94,.15);
            color: var(--success-color);
            font-size: .7rem;
            padding: .25em .6em;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-private {
            background: rgba(139,155,180,.1);
            color: var(--text-muted);
            font-size: .7rem;
            padding: .25em .6em;
            border-radius: 20px;
            font-weight: 600;
        }

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

        /* ── Tableaux ── */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--card-border);
            --bs-table-striped-bg: rgba(239,183,2,.05);
            --bs-table-hover-bg: rgba(239,183,2,.08);
            color: var(--text-primary);
            background: var(--accent);
            border-radius: 0;
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
            padding: 14px 16px;
        }

        .table tbody { background: #212227; }

        .table tbody td {
            background: #212227;
            color: var(--text-primary);
            padding: 10px 16px;
        }

        .breadcrumb { background-color: #212227; }

        /* ── Footer ── */
        #main-footer {
            border-top: 1px solid var(--card-border);
            padding: 1.25rem 1.5rem;
            text-align: center;
            font-size: .78rem;
            color: var(--text-muted);
            background: var(--header-bg);
        }

        #main-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        #main-footer a:hover { text-decoration: underline; }

        /* ── Navbar toggler custom ── */
        .navbar-toggler {
            border: 1px solid rgba(239,183,2,.4);
            color: var(--accent);
            border-radius: 0;
            padding: .3rem .55rem;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23EFB702' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* ── Séparateur vertical entre nav et user ── */
        .nav-sep {
            width: 1px;
            height: 24px;
            background: rgba(239,183,2,.2);
            margin: 0 .5rem;
        }
    </style>
</head>
<body>

{{-- ─── Header ─────────────────────────────────────────────── --}}
<header id="main-header">
    <nav class="navbar navbar-expand-lg px-3 py-0 h-100">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="navbar-brand me-4">
            Mn<span>émo</span>
        </a>

        {{-- Hamburger mobile --}}
        <button class="navbar-toggler ms-auto me-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            {{-- Navigation principale --}}
            <div class="d-flex flex-column flex-lg-row align-items-lg-stretch py-2 py-lg-0">
                @php
                    $navItems = [];
                    try {
                        $navItems = \App\Models\NavItem::where('is_active', true)->orderBy('position')->get();
                    } catch (\Throwable $e) {
                        $navItems = [];
                    }
                @endphp

                @if (count($navItems) > 0)
                    @foreach ($navItems as $navItem)
                        <a href="{{ $navItem->url }}"
                           class="nav-link-custom {{ request()->is(ltrim($navItem->url, '/')) ? 'active' : '' }}"
                           @if ($navItem->open_new_tab) target="_blank" rel="noopener noreferrer" @endif>
                            @if ($navItem->icon)<i class="bi {{ $navItem->icon }}"></i> @endif
                            {{ $navItem->label }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('dashboard') }}"
                       class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Accueil
                    </a>
                    <a href="{{ route('modules.index') }}"
                       class="nav-link-custom {{ request()->routeIs('modules.*') || request()->routeIs('test.*') || request()->routeIs('anki.*') ? 'active' : '' }}">
                        <i class="bi bi-collection"></i> Mes modules
                    </a>
                    <a href="{{ route('library.index') }}"
                       class="nav-link-custom {{ request()->routeIs('library.*') ? 'active' : '' }}">
                        <i class="bi bi-globe2"></i> Bibliothèque
                    </a>
                    <a href="{{ route('progress.index') }}"
                       class="nav-link-custom {{ request()->routeIs('progress.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i> Progression
                    </a>
                @endif
            </div>

            {{-- Utilisateur (droite) --}}
            @auth
            <div class="ms-lg-auto d-flex align-items-center py-2 py-lg-0">
                <div class="nav-sep d-none d-lg-block"></div>
                <div class="user-avatar mx-2">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="dropdown">
                    <button class="d-flex align-items-center gap-1"
                            style="background:none;border:none;color:var(--text-primary);font-weight:700;font-size:.75rem;text-transform:uppercase;letter-spacing:.8px;cursor:pointer;"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                        <i class="bi bi-chevron-down" style="font-size:.65rem;color:var(--text-muted);"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i> Mon profil
                            </a>
                        </li>
                        @if (Auth::user()->isAdmin())
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.dashboard') }}"
                               style="color:var(--accent);">
                                <i class="bi bi-shield-check"></i> Administration
                            </a>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"
                                        style="background:none;border:none;width:100%;text-align:left;color:#ef4444;">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            @endauth

        </div>
    </nav>
</header>

{{-- ─── Contenu principal ─────────────────────────────────── --}}
<div id="main-content">

    {{-- Messages flash --}}
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

    <div class="page-content">
        {{ $slot }}
    </div>

    <footer id="main-footer">
        <div>© Copyright - Tout droit réservé</div>
        <div>Fait avec ♥️ par <a href="https://github.com/YoannFM-rascol/" target="_blank" rel="noopener noreferrer">YoannFM</a></div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
