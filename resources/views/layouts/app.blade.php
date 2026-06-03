<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo - Apprenez par la répétition espacée' }}</title>

    {{-- SEO - méta descriptions et mots-clés --}}
    <meta name="description" content="{{ isset($metaDescription) ? $metaDescription : 'Mnémo est une application de mémorisation par répétition espacée. Créez vos modules, apprenez avec le mode Anki ou testez vos connaissances.' }}">
    <meta name="keywords" content="mémorisation, répétition espacée, anki, flashcard, apprentissage, quiz, mnémo">
    <meta name="author" content="YoannFM">
    <meta name="robots" content="index, follow">

    {{-- Open Graph (partage sur réseaux sociaux) --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo - Apprenez par la répétition espacée' }}">
    <meta property="og:description" content="Application de mémorisation par répétition espacée. Créez des modules, entraînez-vous en mode Anki et suivez votre progression.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Mnémo">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo' }}">
    <meta name="twitter:description" content="Application de mémorisation par répétition espacée.">

    {{-- Google Fonts : Rubik --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ============================================================
           Variables CSS - palette ModernPro
           Accent dore #EFB702 sur fond sombre #2E2E34
           ============================================================ */
        :root {
            --sidebar-bg: #212227;
            --sidebar-width: 260px;
            --sidebar-border: #2E2E34;
            --accent: #EFB702;
            --accent-hover: #d6a502;
            --accent-light: rgba(239,183,2,.12);
            --text-primary: #DDDDDD;
            --text-muted: #9ca3af;
            --card-bg: #212227;
            --card-border: #2E2E34;
            --body-bg: #2E2E34;
            --success-color: #22c55e;
            --danger-color: #ff5956;
        }

        /* ============================================================
           Base
           ============================================================ */
        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        ::selection {
            background: var(--accent);
            color: #212227;
        }

        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: var(--sidebar-bg); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Rubik', sans-serif;
            font-weight: 700;
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
            border: 2px solid var(--accent);
            border-radius: 5px;
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

        @media (max-width: 768px) {
            .btn { min-height: 44px; }
        }

        /* ============================================================
           Formulaires
           ============================================================ */
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

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-label {
            font-weight: 600;
            font-size: .875rem;
            color: var(--text-muted);
            margin-bottom: .4rem;
            text-transform: uppercase;
            letter-spacing: .5px;
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
           Tableaux Bootstrap - forcer le dark theme
           Bootstrap utilise des variables --bs-table-* par défaut
           qui donnent un fond blanc. On les réécrit ici.
           ============================================================ */
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

        .table tbody {
            background: #212227;
        }

        .table tbody td {
            background: #212227;
            color: var(--text-primary);
            padding: 10px 16px;
        }

        .breadcrumb {
            background-color: #212227;
        }

        /* ============================================================
           Footer
           ============================================================ */
        #main-footer {
            border-top: 1px solid var(--card-border);
            padding: 1.25rem 1.5rem;
            text-align: center;
            font-size: .78rem;
            color: var(--text-muted);
            background: var(--sidebar-bg);
        }

        #main-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        #main-footer a:hover {
            text-decoration: underline;
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

    {{-- Footer global - affiché sur toutes les pages --}}
    <footer id="main-footer">
        <div>© Copyright - Tout droit réservé</div>
        <div>Fait avec ♥️ par <a href="https://github.com/YoannFM-rascol/" target="_blank" rel="noopener noreferrer">YoannFM</a></div>
    </footer>

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
