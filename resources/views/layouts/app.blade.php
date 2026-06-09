<!DOCTYPE html>
<html lang="fr" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo - Apprenez par la répétition espacée' }}</title>

    <meta name="description" content="{{ isset($metaDescription) ? $metaDescription : setting('site_description', 'Mnémo est une application de mémorisation par répétition espacée.') }}">
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

    @includeIf('social-share::meta')

    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: {{ auth()->check() && auth()->user()->accent_color ? auth()->user()->accent_color : setting('theme_accent', '#EFB702') }};
            --accent-hover: color-mix(in srgb, var(--accent) 85%, black);
            --accent-light: color-mix(in srgb, var(--accent) 15%, transparent);
            --text-primary: {{ setting('theme_text_color', '#e2e8f0') }};
            --text-muted: #9ca3af;
            --card-bg: {{ setting('theme_card_bg', '#212227') }};
            --card-border: {{ setting('theme_content_bg', '#2E2E34') }};
            --body-bg: {{ setting('theme_body_bg', '#111113') }};
            --header-bg: {{ setting('theme_header_bg', '#1a1b1f') }};
            --success-color: #22c55e;
            --danger-color: #ff5956;
        }

        [data-bs-theme="light"] {
            --body-bg: {{ setting('theme_light_body_bg', '#f0f2f5') }};
            --card-bg: {{ setting('theme_light_card_bg', '#ffffff') }};
            --card-border: {{ setting('theme_light_content_bg', '#e9ecef') }};
            --header-bg: {{ setting('theme_light_header_bg', '#ffffff') }};
            --text-primary: {{ setting('theme_light_text_color', '#212529') }};
            --text-muted: #6c757d;
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
            background: var(--card-border);
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

        /* ── Mobile menu plein écran ── */
        #mobile-menu {
            position: fixed;
            inset: 0;
            background: var(--header-bg);
            z-index: 1000;
            flex-direction: column;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            transform: translateX(-100%);
            transition: transform .35s cubic-bezier(.4,0,.2,1), visibility .35s;
            visibility: hidden;
            pointer-events: none;
        }
        #mobile-menu.open {
            transform: translateX(0);
            visibility: visible;
            pointer-events: all;
        }

        #mobile-menu-close {
            background: none;
            border: none;
            color: var(--accent);
            font-size: 1.75rem;
            line-height: 1;
            align-self: flex-end;
            cursor: pointer;
            padding: 0;
        }

        #mobile-menu .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            padding: 1rem 0;
        }
        #mobile-menu .mobile-nav-link.active {
            color: var(--accent);
            border-left: 3px solid var(--accent);
            margin-left: -1.5rem;
            padding-left: calc(1.5rem - 3px);
        }
        #mobile-menu .mobile-nav-link i { font-size: 1.1rem; width: 24px; text-align: center; }

        #mobile-menu .mobile-user-section {
            margin-top: 0;
            display: flex;
            flex-direction: column;
        }
        #mobile-menu .mobile-user-section a,
        #mobile-menu .mobile-user-section button {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--text-muted);
            font-size: .9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: .65rem 0;
            text-decoration: none;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .page-content { padding: 1rem .75rem; }
            .nav-sep { display: none; }
            .table-responsive { font-size: .875rem; }
        }
        @media (max-width: 575.98px) {
            .page-content { padding: .75rem .5rem; }
            h1 { font-size: 1.4rem; }
            h2 { font-size: 1.2rem; }
        }

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
            padding: 1.5rem 1.5rem 1.25rem;
            font-size: .78rem;
            color: var(--text-muted);
            background: var(--header-bg);
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: end;
            gap: .75rem;
        }
        #main-footer .footer-left { grid-column: 1; }
        #main-footer .footer-center { grid-column: 2; text-align: center; }
        @media (max-width: 768px) {
            #main-footer { grid-template-columns: 1fr; }
            #main-footer .footer-left { grid-column: 1; order: 2; }
            #main-footer .footer-center { grid-column: 1; order: 1; }
        }

        #main-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        #main-footer a:hover { text-decoration: underline; }

        /* ── Navbar toggler custom ── */
        .navbar-toggler {
            border: none;
            border-radius: 0;
            padding: .4rem .6rem;
            background: none;
            display: flex;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            box-shadow: none !important;
            outline: none !important;
        }
        .navbar-toggler .hb-line {
            display: block;
            width: 22px;
            height: 2px;
            background: var(--accent);
            border-radius: 0;
            transition: transform .3s, opacity .3s;
            transform-origin: center;
        }
        .navbar-toggler.is-open .hb-line:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .navbar-toggler.is-open .hb-line:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .navbar-toggler.is-open .hb-line:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
        .navbar-toggler-icon { display: none; }

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

@php
    $navItems = [];
    try { $navItems = \App\Models\NavItem::where('is_active', true)->orderBy('position')->get(); } catch (\Throwable $e) {}
@endphp

{{-- ─── Menu mobile plein écran ─── --}}
<div id="mobile-menu" role="dialog" aria-modal="true" aria-label="Menu navigation">
    <button id="mobile-menu-close" aria-label="Fermer le menu">&#x2715;</button>

    <nav style="margin-top:1.5rem;">
        @if (count($navItems) > 0)
            @foreach ($navItems as $navItem)
                <a href="{{ $navItem->getUrl() }}"
                   class="mobile-nav-link {{ request()->is(ltrim($navItem->value ?? '', '/')) ? 'active' : '' }}"
                   @if($navItem->new_tab) target="_blank" rel="noopener noreferrer" @endif>
                    @if($navItem->icon)<i class="bi {{ $navItem->icon }}"></i>@endif
                    {{ $navItem->label }}
                </a>
            @endforeach
        @else
            <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Accueil
            </a>
            <a href="{{ route('modules.index') }}" class="mobile-nav-link {{ request()->routeIs('modules.*') || request()->routeIs('test.*') || request()->routeIs('anki.*') ? 'active' : '' }}">
                <i class="bi bi-collection"></i> Mes modules
            </a>
            <a href="{{ route('library.index') }}" class="mobile-nav-link {{ request()->routeIs('library.*') ? 'active' : '' }}">
                <i class="bi bi-globe2"></i> Bibliothèque
            </a>
            <a href="{{ route('progress.index') }}" class="mobile-nav-link {{ request()->routeIs('progress.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Progression
            </a>
            <a href="{{ route('shared-exam.index') }}" class="mobile-nav-link {{ request()->routeIs('shared-exam.*') ? 'active' : '' }}">
                <i class="bi bi-share"></i> Examens
            </a>
            @if(\App\Models\Setting::get('feature_groups', '1'))
            <a href="{{ route('groups.index') }}" class="mobile-nav-link {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Groupes
            </a>
            @endif
        @endif
    </nav>

    @auth
    <div class="mobile-user-section">
        <button id="mobile-user-toggle" style="background:none;border:none;width:100%;text-align:left;padding:1rem 0;cursor:pointer;display:flex;align-items:center;gap:.75rem;">
            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <span style="font-weight:700;color:var(--text-primary);font-size:1rem;text-transform:uppercase;letter-spacing:.5px;">{{ Auth::user()->name }}</span>
            <i class="bi bi-chevron-down" id="mobile-user-chevron" style="color:var(--text-muted);margin-left:auto;transition:transform .2s;"></i>
        </button>
        <div id="mobile-user-items" style="display:none;background:var(--card-bg);border-radius:8px;padding:.25rem .75rem;margin-top:.25rem;">
            <a href="{{ route('notifications.index') }}" class="mobile-nav-link">
                <i class="bi bi-bell"></i> Notifications
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="badge bg-danger ms-1">{{ $unreadNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('profile.edit') }}" class="mobile-nav-link"><i class="bi bi-person"></i> Mon profil</a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link" style="color:var(--accent);"><i class="bi bi-shield-check"></i> Administration</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mobile-nav-link" style="background:none;border:none;width:100%;text-align:left;color:#ef4444;"><i class="bi bi-box-arrow-right"></i> Déconnexion</button>
            </form>
        </div>
    </div>
    @endauth
</div>

{{-- ─── Header ─────────────────────────────────────────────── --}}
<header id="main-header">
    <nav class="navbar navbar-expand-lg px-3 py-0 h-100">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="navbar-brand me-4">
            Mn<span>émo</span>
        </a>

        {{-- Bouton hamburger mobile --}}
        <button id="mobile-menu-open" class="navbar-toggler d-lg-none ms-auto me-2" type="button" aria-label="Ouvrir le menu">
            <span class="hb-line"></span>
            <span class="hb-line"></span>
            <span class="hb-line"></span>
        </button>

        {{-- Nav desktop uniquement --}}
        <div class="d-none d-lg-flex align-items-stretch flex-grow-1" id="navbarDesktop">

            <div class="d-flex align-items-stretch">
                @if (count($navItems) > 0)
                    @foreach ($navItems as $navItem)
                        <a href="{{ $navItem->getUrl() }}"
                           class="nav-link-custom {{ request()->is(ltrim($navItem->value ?? '', '/')) ? 'active' : '' }}"
                           @if($navItem->new_tab) target="_blank" rel="noopener noreferrer" @endif>
                            @if($navItem->icon)<i class="bi {{ $navItem->icon }}"></i>@endif
                            {{ $navItem->label }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Accueil
                    </a>
                    <a href="{{ route('modules.index') }}" class="nav-link-custom {{ request()->routeIs('modules.*') || request()->routeIs('test.*') || request()->routeIs('anki.*') ? 'active' : '' }}">
                        <i class="bi bi-collection"></i> Mes modules
                    </a>
                    <a href="{{ route('library.index') }}" class="nav-link-custom {{ request()->routeIs('library.*') ? 'active' : '' }}">
                        <i class="bi bi-globe2"></i> Bibliothèque
                    </a>
                    <a href="{{ route('progress.index') }}" class="nav-link-custom {{ request()->routeIs('progress.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i> Progression
                    </a>
                    @if(\App\Models\Setting::get('feature_groups', '1'))
                    <a href="{{ route('groups.index') }}" class="nav-link-custom {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Groupes
                    </a>
                    @endif
                    <a href="{{ route('shared-exam.index') }}" class="nav-link-custom {{ request()->routeIs('shared-exam.*') ? 'active' : '' }}">
                        <i class="bi bi-share"></i> Examens
                    </a>
                @endif
            </div>

            @auth
            <div class="ms-auto d-flex align-items-center gap-1">
                <button id="theme-toggle"
                        style="background:none;border:none;color:var(--text-muted);font-size:1rem;cursor:pointer;padding:0 .5rem;"
                        title="Changer le theme" aria-label="Basculer theme">
                    <i class="bi bi-sun-fill"></i>
                </button>

                <div class="dropdown">
                    <button class="d-flex align-items-center gap-2 px-2"
                            style="background:none;border:none;cursor:pointer;"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="position-relative">
                            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            @if(($unreadNotifications ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem;">
                                    {{ $unreadNotifications }}
                                </span>
                            @endif
                        </div>
                        <span class="d-none d-lg-inline" style="color:var(--text-primary);font-size:.9rem;font-weight:600;">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom">
                        <li>
                            <span class="dropdown-item" style="color:var(--text-muted);font-size:.75rem;cursor:default;pointer-events:none;">
                                {{ Auth::user()->name }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell"></i> Notifications
                                @if(($unreadNotifications ?? 0) > 0)
                                    <span class="badge bg-danger ms-1">{{ $unreadNotifications }}</span>
                                @endif
                            </a>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('my-exam-results') }}"><i class="bi bi-clipboard-check"></i> Mes résultats</a></li>
                        @if(Auth::user()->isAdmin())
                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}" style="color:var(--accent);"><i class="bi bi-shield-check"></i> Administration</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="background:none;border:none;width:100%;text-align:left;color:#ef4444;">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            @endauth
        </div>

        {{-- Notifications visible sur mobile dans la barre --}}
        @auth
        <a class="nav-link position-relative d-lg-none" href="{{ route('notifications.index') }}" style="color:var(--text-muted);">
            <i class="bi bi-bell-fill"></i>
            @if(($unreadNotifications ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">{{ $unreadNotifications }}</span>
            @endif
        </a>
        @endauth

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
        <div class="footer-left"></div>
        <div class="footer-center">
            <div>© 2026 YoannFM · <a href="#" data-bs-toggle="modal" data-bs-target="#licenseModal">Licence MIT</a></div>
            <div style="margin-top:.2rem;">Fait avec ❤️ par <a href="https://github.com/YoannFM-rascol/" target="_blank" rel="noopener noreferrer">YoannFM</a></div>
        </div>
    </footer>

    {{-- Modal Licence MIT --}}
    <div class="modal fade" id="licenseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Licence MIT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre style="font-size:.82rem;white-space:pre-wrap;word-break:break-word;margin:0;">{{ file_get_contents(base_path('LICENSE')) }}</pre>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function() {
    var theme = localStorage.getItem('theme') || 'dark';
    document.getElementById('html-root').setAttribute('data-bs-theme', theme);
    var btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = theme === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
})();
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle
    var btn = document.getElementById('theme-toggle');
    if (btn) {
        var theme = localStorage.getItem('theme') || 'dark';
        btn.innerHTML = theme === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
        btn.addEventListener('click', function() {
            var current = document.getElementById('html-root').getAttribute('data-bs-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            document.getElementById('html-root').setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            this.innerHTML = next === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
        });
    }

    // Menu mobile plein écran
    var menu = document.getElementById('mobile-menu');
    var openBtn = document.getElementById('mobile-menu-open');
    var closeBtn = document.getElementById('mobile-menu-close');
    if (menu && openBtn && closeBtn) {
        openBtn.addEventListener('click', function() {
            menu.classList.add('open');
            openBtn.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });
        closeBtn.addEventListener('click', function() {
            menu.classList.remove('open');
            openBtn.classList.remove('is-open');
            document.body.style.overflow = '';
        });
    }

    // Section utilisateur mobile repliable
    var userToggle = document.getElementById('mobile-user-toggle');
    var userItems = document.getElementById('mobile-user-items');
    var userChevron = document.getElementById('mobile-user-chevron');
    if (userToggle && userItems) {
        userToggle.addEventListener('click', function() {
            var open = userItems.style.display === 'none';
            userItems.style.display = open ? 'block' : 'none';
            if (userChevron) userChevron.style.transform = open ? 'rotate(180deg)' : '';
        });
    }
});
</script>

</body>
</html>
