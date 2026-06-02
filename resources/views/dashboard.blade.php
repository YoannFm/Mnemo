<x-app-layout>
    {{-- Titre affiché dans la topbar --}}
    <x-slot name="pageTitle">Tableau de bord</x-slot>

    {{-- ── En-tête de bienvenue ── --}}
    <div class="mb-4">
        <h4 class="mb-1">Bonjour, {{ Auth::user()->name }} 👋</h4>
        <p style="color: var(--text-muted); font-size:.9rem;">
            Prêt à mémoriser quelque chose aujourd'hui ?
        </p>
    </div>

    {{-- ── Statistiques rapides ── --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:var(--accent-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-collection" style="color:var(--accent);font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem;font-weight:700;">{{ Auth::user()->modules()->count() }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Modules</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(34,197,94,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-card-list" style="color:#22c55e;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem;font-weight:700;">
                            {{ Auth::user()->modules()->withCount('items')->get()->sum('items_count') }}
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Items total</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(251,191,36,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-globe2" style="color:#fbbf24;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem;font-weight:700;">
                            {{ Auth::user()->modules()->where('is_public', true)->count() }}
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Publics</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(168,85,247,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-bar-chart" style="color:#a855f7;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem;font-weight:700;">{{ Auth::user()->scores()->count() }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Tests effectués</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Derniers modules ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Mes derniers modules</h5>
        <a href="{{ route('modules.index') }}" class="btn btn-sm btn-outline-primary">
            Voir tout
        </a>
    </div>

    @php
        $recentModules = Auth::user()->modules()->withCount('items')->latest()->limit(6)->get();
    @endphp

    @if ($recentModules->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-collection" style="font-size:3rem;color:var(--text-muted);"></i>
            <p class="mt-3" style="color:var(--text-muted);">Vous n'avez pas encore de module.</p>
            <a href="{{ route('modules.create') }}" class="btn btn-primary mx-auto" style="width:fit-content;">
                <i class="bi bi-plus-lg me-1"></i> Créer mon premier module
            </a>
        </div>
    @else
        <div class="row g-3">
            @foreach ($recentModules as $module)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column gap-2 p-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <h6 class="mb-0 fw-semibold">{{ $module->title }}</h6>
                                @if ($module->is_public)
                                    <span class="badge-public">Public</span>
                                @else
                                    <span class="badge-private">Privé</span>
                                @endif
                            </div>
                            @if ($module->description)
                                <p style="font-size:.8rem;color:var(--text-muted);margin:0;" class="text-truncate">
                                    {{ $module->description }}
                                </p>
                            @endif
                            <div style="font-size:.78rem;color:var(--text-muted);">
                                <i class="bi bi-card-list me-1"></i>
                                {{ $module->items_count }} item{{ $module->items_count > 1 ? 's' : '' }}
                            </div>
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
