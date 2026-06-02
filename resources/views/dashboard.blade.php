<x-app-layout>
    {{-- Titre affiché dans la topbar du navigateur --}}
    <x-slot name="pageTitle">Tableau de bord</x-slot>

    {{-- En-tête de bienvenue avec le nom de l'utilisateur connecté --}}
    {{-- Affiche un message motivant pour encourager l'apprentissage --}}
    <div class="mb-4">
        <h4 class="mb-1">Bonjour, {{ Auth::user()->name }} 👋</h4>
        <p style="color: var(--text-muted); font-size:.9rem;">
            Prêt à mémoriser quelque chose aujourd'hui ?
        </p>
    </div>

    {{-- Statistiques rapides du tableau de bord --}}
    {{-- Affiche 4 KPIs : modules, items, modules publics, tests effectués --}}
    {{-- Ces stats sont calculées en temps réel depuis la base de données --}}
    <div class="row g-3 mb-4">

        {{-- Stat 1 : Nombre de modules créés --}}
        {{-- Cliquable pour aller à la liste complète des modules --}}
        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:var(--accent-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-collection" style="color:var(--accent);font-size:1.2rem;"></i>
                    </div>
                    <div>
                        {{-- Compteur du nombre total de modules de l'utilisateur --}}
                        <div style="font-size:1.5rem;font-weight:700;">{{ Auth::user()->modules()->count() }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Modules</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat 2 : Total des items dans tous les modules --}}
        {{-- Somme de tous les items de tous les modules --}}
        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(34,197,94,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-card-list" style="color:#22c55e;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        {{-- Calcul : pour chaque module, compter ses items, puis additionner --}}
                        <div style="font-size:1.5rem;font-weight:700;">
                            {{ Auth::user()->modules()->withCount('items')->get()->sum('items_count') }}
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Items total</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat 3 : Nombre de modules publics (partagés en bibliothèque) --}}
        {{-- Seuls les modules avec is_public = true sont comptés --}}
        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(251,191,36,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-globe2" style="color:#fbbf24;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        {{-- Compteur des modules publics seulement --}}
                        <div style="font-size:1.5rem;font-weight:700;">
                            {{ Auth::user()->modules()->where('is_public', true)->count() }}
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Publics</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat 4 : Nombre total de tests effectués en mode Test --}}
        {{-- Ne compte que les sessions terminées (scores enregistrés) --}}
        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(168,85,247,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-bar-chart" style="color:#a855f7;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        {{-- Compteur du nombre de scores enregistrés (tests terminés) --}}
                        <div style="font-size:1.5rem;font-weight:700;">{{ Auth::user()->scores()->count() }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Tests effectués</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Section "Mes derniers modules" --}}
    {{-- Affiche les 6 modules les plus récemment créés/modifiés --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Mes derniers modules</h5>
        {{-- Lien vers la liste complète de tous les modules --}}
        <a href="{{ route('modules.index') }}" class="btn btn-sm btn-outline-primary">
            Voir tout
        </a>
    </div>

    {{-- Récupère les 6 derniers modules de l'utilisateur avec le compte des items --}}
    {{-- Trier par date de création/modification (latest) pour montrer les plus récents en premier --}}
    @php
        $recentModules = Auth::user()->modules()->withCount('items')->latest()->limit(6)->get();
    @endphp

    {{-- Afficher un message s'il n'y a pas de module --}}
    @if ($recentModules->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-collection" style="font-size:3rem;color:var(--text-muted);"></i>
            <p class="mt-3" style="color:var(--text-muted);">Vous n'avez pas encore de module.</p>
            {{-- Bouton pour créer le premier module --}}
            <a href="{{ route('modules.create') }}" class="btn btn-primary mx-auto" style="width:fit-content;">
                <i class="bi bi-plus-lg me-1"></i> Créer mon premier module
            </a>
        </div>
    @else
        {{-- Grille de cartes affichant les derniers modules --}}
        {{-- Chaque carte contient : titre, badge public/privé, description, nombre d'items, bouton ouvrir --}}
        <div class="row g-3">
            @foreach ($recentModules as $module)
                {{-- Carte d'un module individuel --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column gap-2 p-3">
                            {{-- En-tête : titre + badge --}}
                            <div class="d-flex align-items-start justify-content-between">
                                <h6 class="mb-0 fw-semibold">{{ $module->title }}</h6>
                                {{-- Badge public/privé selon le statut du module --}}
                                @if ($module->is_public)
                                    <span class="badge-public">Public</span>
                                @else
                                    <span class="badge-private">Privé</span>
                                @endif
                            </div>
                            {{-- Description courte (tronquée à 1 ligne) --}}
                            @if ($module->description)
                                <p style="font-size:.8rem;color:var(--text-muted);margin:0;" class="text-truncate">
                                    {{ $module->description }}
                                </p>
                            @endif
                            {{-- Nombre d'items dans le module --}}
                            <div style="font-size:.78rem;color:var(--text-muted);">
                                <i class="bi bi-card-list me-1"></i>
                                {{ $module->items_count }} item{{ $module->items_count > 1 ? 's' : '' }}
                            </div>
                            {{-- Bouton pour ouvrir le détail du module --}}
                            <a href="{{ route('modules.show', $module) }}" class="btn btn-sm btn-outline-primary mt-auto">
                                Ouvrir
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-app-layout>
