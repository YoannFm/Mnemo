<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - Admin' : 'Administration - Mnemo' }}</title>

    <script src="{{ asset('assets/vendor/admin.js') }}" defer></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/vendor/admin.css') }}" rel="stylesheet">
</head>
<body>
    <div class="wrapper">

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

                    <li class="sidebar-header">Général</li>

                    <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> <span>Tableau de bord</span>
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

                    <li class="sidebar-item {{ request()->routeIs('admin.images.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('admin.images.index') }}">
                            <i class="bi bi-images"></i> <span>Images</span>
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
                    <div class="d-none d-sm-inline-block">
                        <a href="https://discord.gg/HtjPAfqUXu" class="btn btn-outline-primary mx-1" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-question-circle"></i> Support
                        </a>
                        <a href="https://wiki.novadev.ovh" class="btn btn-outline-info mx-1" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-book"></i> Documentation
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mx-1">
                            <i class="bi bi-arrow-left"></i>
                        </a>
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
                    <p class="mb-0 py-2 text-center text-muted">
                        Mnemo - Administration
                    </p>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
