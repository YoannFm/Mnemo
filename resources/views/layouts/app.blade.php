<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - Mnémo' : 'Mnémo' }}</title>

    {{-- Google Fonts : Inter pour le corps, Poppins pour les titres --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ============================================================
           Variables CSS - palette inspirée ModernPro/Azurium
           Fond sombre avec accents bleu-violet (#6366f1 = Indigo)
           ============================================================ */
        :root {
            --sidebar-bg: #0f1117;
            --sidebar-width: 260px;
            --sidebar-border: #1e2130;
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --accent-light: rgba(99,102,241,.12);
            --text-primary: #f1f5f9;
            --text-muted: #8b9bb4;
            --card-bg: #181c2a;
            --card-border: #252a3d;
            --body-bg: #13162b;
            --success-color: #22c55e;
            --danger-color: #ef4444;
        }

        /* ============================================================
           Base
           ============================================================ */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6, .fw-bold {
            font-family: 'Poppins', sans-serif;
        }

        /* ============================================================
           Sidebar
           ============================================================ */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }

        /* Logo Mnémo dans la sidebar */
        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -.5px;
            border-bottom: 1px solid var(--sidebar-border);
            text-decoration: none;
        }

        .sidebar-brand span {
            color: var(--text-primary);
        }

        /* Section de navigation */
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
        }

        .nav-section-title {
            font-size: .65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            padding: .5rem 1.25rem .25rem;
        }

        /* Lien de navigation */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1.25rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            border-radius: 0;
            transition: all .2s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            color: var(--text-primary);
            background: var(--accent-light);
        }

        /* Lien actif dans la sidebar */
        .sidebar-link.active {
            color: var(--accent);
            background: var(--accent-light);
            border-left-color: var(--accent);
        }

        .sidebar-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Infos utilisateur en bas de sidebar */
        .sidebar-user {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .875rem;
            color: #fff;
            flex-shrink: 0;
        }

        .user-name {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: .7rem;
            color: var(--text-muted);
        }

        /* ============================================================
           Contenu principal (décalé à droite de la sidebar)
           ============================================================ */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        #topbar {
            background: rgba(15,17,23,.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--sidebar-border);
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        /* Bouton hamburger mobile */
        #sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.3rem;
            cursor: pointer;
        }

        /* Contenu des pages */
        .page-content {
            padding: 2rem 1.5rem;
            flex: 1;
        }

        /* ============================================================
           Cartes (cards Bootstrap personnalisées)
           ============================================================ */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            color: var(--text-primary);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.25rem;
        }

        /* ============================================================
           Boutons
           ============================================================ */
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            font-weight: 500;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
        }

        .btn-outline-primary {
            color: var(--accent);
            border-color: var(--accent);
        }

        .btn-outline-primary:hover {
            background: var(--accent);
            border-color: var(--accent);
        }

        /* Boutons tactiles (plus grands sur mobile) */
        @media (max-width: 768px) {
            .btn {
                min-height: 44px;
            }
        }

        /* ============================================================
           Formulaires
           ============================================================ */
        .form-control, .form-select {
            background: #0f1117;
            border: 1px solid var(--card-border);
            color: var(--text-primary);
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            background: #0f1117;
            border-color: var(--accent);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px var(--accent-light);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-label {
            font-weight: 500;
            font-size: .875rem;
            color: var(--text-muted);
            margin-bottom: .4rem;
        }

        /* ============================================================
           Badges / alertes
           ============================================================ */
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

        /* Alertes flash */
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

        /* ============================================================
           Responsive - sidebar en drawer sur mobile
           ============================================================ */
        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0;
            }

            #sidebar-toggle {
                display: block;
            }

            /* Overlay sombre derrière la sidebar mobile */
            #sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.6);
                z-index: 99;
            }

            #sidebar-overlay.open {
                display: block;
            }
        }
    </style>
</head>
<body>

{{-- ─── Sidebar ──────────────────────────────────────────────── --}}
<nav id="sidebar">

    {{-- Logo / Nom de l'application --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        Mn<span>émo</span>
    </a>

    <div class="sidebar-nav">

        {{-- Navigation principale --}}
        <p class="nav-section-title">Navigation</p>

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Tableau de bord
        </a>

        <a href="{{ route('modules.index') }}"
           class="sidebar-link {{ request()->routeIs('modules.*') || request()->routeIs('test.*') || request()->routeIs('anki.*') ? 'active' : '' }}">
            <i class="bi bi-collection"></i>
            Mes modules
        </a>

        <a href="{{ route('library.index') }}"
           class="sidebar-link {{ request()->routeIs('library.*') ? 'active' : '' }}">
            <i class="bi bi-globe2"></i>
            Bibliothèque
        </a>

        <a href="{{ route('progress.index') }}"
           class="sidebar-link {{ request()->routeIs('progress.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i>
            Ma progression
        </a>

    </div>

    {{-- Infos utilisateur en pied de sidebar --}}
    @auth
    <div class="sidebar-user">
        <div class="user-avatar">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div class="flex-grow-1 overflow-hidden">
            <div class="user-name text-truncate">{{ Auth::user()->name }}</div>
            <div class="user-role">Utilisateur</div>
        </div>
        {{-- Bouton déconnexion --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm p-1" style="color: var(--text-muted);" title="Déconnexion">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
    @endauth
</nav>

{{-- Overlay mobile (se ferme au clic) --}}
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

{{-- ─── Contenu principal ────────────────────────────────────── --}}
<div id="main-content">

    {{-- Topbar --}}
    <div id="topbar">
        {{-- Hamburger menu (visible seulement sur mobile) --}}
        <button id="sidebar-toggle" onclick="toggleSidebar()" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>

        {{-- Titre de la page (slot optionnel) --}}
        <div class="fw-semibold" style="font-size:.9rem; color: var(--text-muted);">
            {{ $pageTitle ?? config('app.name') }}
        </div>

        {{-- Liens rapides topbar --}}
        @auth
        <a href="{{ route('profile.edit') }}" class="text-decoration-none" style="color: var(--text-muted);">
            <i class="bi bi-person-circle" style="font-size:1.3rem;"></i>
        </a>
        @endauth
    </div>

    {{-- Messages flash (succès / erreur) --}}
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

    {{-- Contenu de la page injecté via $slot --}}
    <div class="page-content">
        {{ $slot }}
    </div>

</div>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /* ──────────────────────────────────────────────
       Gestion de la sidebar mobile (hamburger menu)
       ────────────────────────────────────────────── */

    /**
     * Ouvre ou ferme la sidebar sur mobile.
     * Ajoute/retire la classe "open" sur la sidebar et l'overlay.
     */
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebar-overlay').classList.toggle('open');
    }

    /**
     * Ferme la sidebar mobile (clic sur l'overlay).
     */
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('open');
    }
</script>

</body>
</html>
