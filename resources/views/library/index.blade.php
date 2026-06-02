<x-app-layout>
    <x-slot name="pageTitle">Bibliothèque publique</x-slot>

    {{-- En-tête --}}
    <div class="mb-4">
        <h4 class="mb-1">Bibliothèque publique</h4>
        <p style="color:var(--text-muted);font-size:.875rem;">
            Parcourez et entraînez-vous sur les modules partagés par la communauté.
        </p>
    </div>

    {{-- Barre de recherche --}}
    <form method="GET" action="{{ route('library.index') }}" class="mb-4">
        <div class="input-group">
            <input type="text"
                   name="q"
                   class="form-control"
                   placeholder="Rechercher un module…"
                   value="{{ $search ?? '' }}">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
            @if ($search)
                <a href="{{ route('library.index') }}" class="btn" style="color:var(--text-muted);border:1px solid var(--card-border);">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- Résultats --}}
    @if ($modules->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-globe2" style="font-size:3rem;color:var(--text-muted);"></i>
            <h6 class="mt-3">
                {{ $search ? 'Aucun résultat pour "' . $search . '"' : 'Aucun module public pour l\'instant.' }}
            </h6>
            <p style="color:var(--text-muted);font-size:.875rem;">
                Soyez le premier à partager un module !
            </p>
        </div>
    @else
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;">
            {{ $modules->total() }} module{{ $modules->total() > 1 ? 's' : '' }} trouvé{{ $modules->total() > 1 ? 's' : '' }}
            @if ($search) pour « {{ $search }} » @endif
        </div>

        <div class="row g-3">
            @foreach ($modules as $module)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column p-3" style="gap:.75rem;">

                            {{-- Titre + badge public --}}
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <h6 class="mb-0 fw-semibold">{{ $module->title }}</h6>
                                <span class="badge-public flex-shrink-0">
                                    <i class="bi bi-globe2 me-1"></i>Public
                                </span>
                            </div>

                            {{-- Description --}}
                            @if ($module->description)
                                <p style="font-size:.8rem;color:var(--text-muted);margin:0;
                                          display:-webkit-box;-webkit-line-clamp:2;
                                          -webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $module->description }}
                                </p>
                            @endif

                            {{-- Infos : items + auteur --}}
                            <div style="font-size:.78rem;color:var(--text-muted);">
                                <i class="bi bi-card-list me-1"></i>
                                {{ $module->items_count }} item{{ $module->items_count > 1 ? 's' : '' }}
                                &nbsp;·&nbsp;
                                <i class="bi bi-person me-1"></i>
                                {{ $module->owner->name }}
                            </div>

                            {{-- Boutons --}}
                            <div class="d-flex gap-2 mt-auto flex-wrap">
                                <a href="{{ route('modules.show', $module) }}"
                                   class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-eye me-1"></i>Voir
                                </a>
                                {{-- Dupliquer ce module dans son espace personnel --}}
                                <form method="POST" action="{{ route('modules.duplicate', $module) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="color:var(--accent);border:1px solid var(--accent);"
                                            title="Copier ce module dans mon espace">
                                        <i class="bi bi-copy me-1"></i>Dupliquer
                                    </button>
                                </form>
                                @if ($module->items_count >= 4)
                                    <a href="{{ route('test.show', $module) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-lightning-charge"></i>
                                    </a>
                                    <a href="{{ route('anki.show', $module) }}"
                                       class="btn btn-sm"
                                       style="color:var(--accent);border:1px solid var(--accent);">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $modules->links('pagination::bootstrap-5') }}
        </div>
    @endif

</x-app-layout>
