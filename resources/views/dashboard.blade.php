<x-app-layout>
    <x-slot name="pageTitle">Tableau de bord</x-slot>

    <div class="mb-5">
        <h3 class="mb-1" style="font-weight:800;letter-spacing:-.5px;">
            Bonjour, <span style="color:var(--accent);">{{ Auth::user()->name }}</span>
        </h3>
        <p style="color:var(--text-muted);font-size:.9rem;margin:0;">
            Prêt à mémoriser quelque chose aujourd'hui ?
        </p>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-5">
        <div class="col-6 col-lg-3">
            <div class="card p-4 text-center">
                <div style="font-size:2.5rem;font-weight:800;color:var(--accent);line-height:1;">
                    {{ Auth::user()->modules()->count() }}
                </div>
                <div style="font-size:.7rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-top:.4rem;">
                    Modules
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card p-4 text-center">
                <div style="font-size:2.5rem;font-weight:800;color:var(--success-color);line-height:1;">
                    {{ Auth::user()->modules()->withCount('items')->get()->sum('items_count') }}
                </div>
                <div style="font-size:.7rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-top:.4rem;">
                    Items
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card p-4 text-center">
                <div style="font-size:2.5rem;font-weight:800;color:#fbbf24;line-height:1;">
                    {{ Auth::user()->modules()->where('is_public', true)->count() }}
                </div>
                <div style="font-size:.7rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-top:.4rem;">
                    Publics
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card p-4 text-center">
                <div style="font-size:2.5rem;font-weight:800;color:#a855f7;line-height:1;">
                    {{ Auth::user()->scores()->count() }}
                </div>
                <div style="font-size:.7rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-top:.4rem;">
                    Tests
                </div>
            </div>
        </div>
    </div>

    {{-- Derniers modules --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0" style="text-transform:uppercase;letter-spacing:1px;font-size:.85rem;color:var(--text-muted);">
            Mes derniers modules
        </h5>
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
                        <div class="card-body d-flex flex-column p-3" style="gap:.75rem;">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <h6 class="mb-0 fw-semibold">{{ $module->title }}</h6>
                                @if ($module->is_public)
                                    <span class="badge-public"><i class="bi bi-globe2 me-1"></i>Public</span>
                                @else
                                    <span class="badge-private"><i class="bi bi-lock me-1"></i>Privé</span>
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
