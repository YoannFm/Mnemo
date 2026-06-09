<!DOCTYPE html>
<html lang="fr" id="admin-html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - Admin' : 'Administration - Mnemo' }}</title>

    <script>
        (function() {
            var t = localStorage.getItem('admin-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <script src="{{ asset('assets/vendor/admin.js') }}" defer></script>

    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/admin.css') }}" rel="stylesheet">
    <style>
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                width: 78vw !important;
                max-width: 320px !important;
                min-width: unset !important;
                z-index: 100;
                margin-left: -78vw !important;
                transition: margin-left .3s ease;
                overflow-y: auto;
            }
            .sidebar.collapsed {
                margin-left: 0 !important;
            }
            .main {
                margin-left: 0 !important;
                width: 100% !important;
                min-width: 0 !important;
            }
            .navbar-bg { position: sticky; top: 0; z-index: 99; width: 100%; }
            .content { padding: 1rem .75rem !important; }
            .table-responsive { font-size: .82rem; }
            .card { margin-bottom: 1rem; }
            h1.h3 { font-size: 1.1rem; }
        }
    </style>
    @stack('header-styles')
</head>
<body>
    <div class="wrapper">

        {{-- Overlay mobile sidebar --}}
        <div id="sidebar-overlay" onclick="document.querySelector('.js-sidebar').classList.remove('collapsed');this.classList.add('d-none');"
             style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;"></div>

        {{-- ── Sidebar ── --}}
        <nav id="sidebar" class="sidebar js-sidebar">
            <div class="sidebar-content js-simplebar">

                <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
                    <div class="sidebar-brand-text mx-3">
                        <span style="font-weight:800;font-size:1.1rem;">Mnémo</span>
                        <small class="d-block text-center">Administration</small>
                    </div>
                </a>

                <ul class="sidebar-nav">

                    <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer"></i> <span>Tableau de bord</span>
                        </a>
                    </li>

                    {{-- Paramètres --}}
                    <li class="sidebar-header">Paramètres</li>

                    <li class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <a class="sidebar-link {{ request()->routeIs('admin.settings.*') ? '' : 'collapsed' }}"
                           href="#" data-bs-toggle="collapse" data-bs-target="#collapseSettings"
                           aria-expanded="{{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }}">
                            <i class="bi bi-gear"></i>
                            <span>Paramètres</span>
                        </a>
                        <ul id="collapseSettings"
                            class="sidebar-dropdown list-unstyled collapse {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}">
                            <li class="sidebar-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('admin.settings.index') }}">Général</a>
                            </li>
                            <li class="sidebar-item {{ request()->routeIs('admin.settings.home') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('admin.settings.home') }}">Accueil</a>
                            </li>
                            <li class="sidebar-item {{ request()->routeIs('admin.settings.auth') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('admin.settings.auth') }}">Authentification</a>
                            </li>
                            <li class="sidebar-item {{ request()->routeIs('admin.settings.mail') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('admin.settings.mail') }}">E-mail</a>
                            </li>
                            <li class="sidebar-item {{ request()->routeIs('admin.settings.maintenance') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('admin.settings.maintenance') }}">Maintenance</a>
                            </li>
                        </ul>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.navbar.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.navbar.index') }}">
                            <i class="bi bi-list-ul"></i> <span>Navigation</span>
                        </a>
                    </li>

                    {{-- Utilisateurs --}}
                    <li class="sidebar-header">Utilisateurs</li>

                    <li class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i> <span>Utilisateurs</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.roles.index') }}">
                            <i class="bi bi-shield-check"></i> <span>Rôles</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.bans.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.bans.index') }}">
                            <i class="bi bi-slash-circle"></i> <span>Bannissements</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.sanctions.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.sanctions.index') }}">
                            <i class="bi bi-exclamation-triangle"></i> <span>Sanctions</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.notifications.index') }}">
                            <i class="bi bi-bell"></i> <span>Notifications</span>
                        </a>
                    </li>

                    {{-- Contenu --}}
                    <li class="sidebar-header">Contenu</li>

                    <li class="sidebar-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.pages.index') }}">
                            <i class="bi bi-file-earmark-text"></i> <span>Pages</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.posts.index') }}">
                            <i class="bi bi-newspaper"></i> <span>Articles</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.reports.index') }}">
                            <i class="bi bi-flag"></i> <span>Signalements</span>
                        </a>
                    </li>


                    <li class="sidebar-item {{ request()->routeIs('admin.comment-history.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.comment-history.index') }}">
                            <i class="bi bi-clock-history"></i> <span>Historique commentaires</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.images.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.images.index') }}">
                            <i class="bi bi-images"></i> <span>Images</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.emojis.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.emojis.index') }}">
                            <i class="bi bi-emoji-smile"></i> <span>Emojis</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.redirects.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.redirects.index') }}">
                            <i class="bi bi-arrow-left-right"></i> <span>Redirections</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.modules.index') }}">
                            <i class="bi bi-collection"></i> <span>Modules publics</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.private-modules.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.private-modules.index') }}">
                            <i class="bi bi-lock"></i> <span>Modules prives</span>
                        </a>
                    </li>

                    {{-- Extensions --}}
                    <li class="sidebar-header">Extensions</li>

                    <li class="sidebar-item {{ request()->routeIs('admin.plugins.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.plugins.index') }}">
                            <i class="bi bi-puzzle"></i> <span>Plugins</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.themes.index') }}">
                            <i class="bi bi-palette"></i> <span>Thèmes</span>
                        </a>
                    </li>

                    {{-- Autres --}}
                    <li class="sidebar-header">Autres</li>

                    <li class="sidebar-item {{ request()->routeIs('admin.update.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.update.index') }}">
                            <i class="bi bi-cloud-download"></i> <span>Mises à jour</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.logs.index') }}">
                            <i class="bi bi-journal-text"></i> <span>Logs</span>
                        </a>
                    </li>

                </ul>
            </div>
        </nav>

        {{-- ── Content Wrapper ── --}}
        <div class="main">

            {{-- Topbar --}}
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>

                <div class="navbar-collapse collapse">
                    <div class="d-flex align-items-center gap-1">
                        <a href="https://discord.gg/HtjPAfqUXu" class="btn btn-outline-primary btn-sm d-none d-sm-inline-flex" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-question-circle"></i> <span class="d-none d-md-inline">Support</span>
                        </a>
                        <a href="https://wiki.novadev.ovh" class="btn btn-outline-info btn-sm d-none d-sm-inline-flex" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-book"></i> <span class="d-none d-md-inline">Documentation</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm mx-1" title="Retour au site">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <button id="admin-theme-toggle" class="btn btn-outline-secondary btn-sm mx-1" title="Changer le theme" aria-label="Basculer theme clair/sombre">
                            <i class="bi bi-moon-fill"></i>
                        </button>
                    </div>

                    <ul class="navbar-nav navbar-align">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name ?? '' }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-circle me-1"></i> Mon profil
                                </a>
                                <a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <i class="bi bi-house me-1"></i> Retour au site
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">

                    <h1 class="h3 mb-3">{{ $pageTitle ?? 'Administration' }}</h1>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{ $slot }}

                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <p class="mb-0 py-1 text-center text-muted" style="font-size:.78rem;">
                        Licence MIT — Copyright (c) 2026 Yoann
                    </p>
                    <p class="mb-0 py-1 text-center text-muted" style="font-size:.78rem;">
                        Fait avec ❤️ par <a href="https://github.com/YoannFM-rascol/" target="_blank" rel="noopener noreferrer">YoannFM</a>
                    </p>
                </div>
            </footer>
        </div>
    </div>
<script>
(function() {
    var theme = localStorage.getItem('admin-theme') || 'light';
    var btn = document.getElementById('admin-theme-toggle');
    if (btn) btn.innerHTML = theme === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
})();
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('admin-theme-toggle');
    if (btn) {
        var theme = localStorage.getItem('admin-theme') || 'light';
        btn.innerHTML = theme === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
        btn.addEventListener('click', function() {
            var current = document.getElementById('admin-html-root').getAttribute('data-bs-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            document.getElementById('admin-html-root').setAttribute('data-bs-theme', next);
            localStorage.setItem('admin-theme', next);
            this.innerHTML = next === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
        });
    }

    // Overlay mobile sidebar - on observe les changements de classe sans re-toggler
    var sidebar = document.querySelector('.js-sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    if (sidebar && overlay) {
        var observer = new MutationObserver(function() {
            if (window.innerWidth < 992) {
                var isOpen = sidebar.classList.contains('collapsed');
                overlay.style.display = isOpen ? 'block' : 'none';
            }
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('collapsed');
            overlay.style.display = 'none';
        });
    }
});
</script>
@stack('footer-scripts')
</body>
</html>
