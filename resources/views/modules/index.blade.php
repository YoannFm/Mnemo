<x-app-layout>
    <x-slot name="pageTitle">Mes modules</x-slot>

    {{-- ── En-tête avec bouton de création ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Mes modules</h4>
            <p style="color:var(--text-muted);font-size:.85rem;margin:0;">
                {{ $modules->total() }} module{{ $modules->total() > 1 ? 's' : '' }} au total
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('modules.import.form') }}"
               class="btn"
               style="color:var(--accent);border:1px solid var(--accent);">
                <i class="bi bi-file-earmark-zip me-1"></i> Importer
            </a>
            <a href="{{ route('modules.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nouveau module
            </a>
        </div>
    </div>

    {{-- ── Grille de modules ── --}}
    @if ($modules->isEmpty())
        {{-- État vide --}}
        <div class="card text-center py-5">
            <i class="bi bi-collection" style="font-size:3.5rem;color:var(--text-muted);"></i>
            <h5 class="mt-3">Aucun module pour l'instant</h5>
            <p style="color:var(--text-muted);font-size:.875rem;">
                Créez votre premier module pour commencer à mémoriser.
            </p>
            <a href="{{ route('modules.create') }}" class="btn btn-primary mx-auto" style="width:fit-content;">
                <i class="bi bi-plus-lg me-1"></i> Créer un module
            </a>
        </div>
    @else
        <div class="row g-3">
            @foreach ($modules as $module)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column p-3" style="gap:.75rem;">

                            {{-- En-tête de la carte : titre + badge --}}
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <h6 class="mb-0 fw-semibold">{{ $module->title }}</h6>
                                @if ($module->is_public)
                                    <span class="badge-public flex-shrink-0">
                                        <i class="bi bi-globe2 me-1"></i>Public
                                    </span>
                                @else
                                    <span class="badge-private flex-shrink-0">
                                        <i class="bi bi-lock me-1"></i>Privé
                                    </span>
                                @endif
                            </div>

                            {{-- Description courte --}}
                            @if ($module->description)
                                <p style="font-size:.8rem;color:var(--text-muted);margin:0;
                                          display:-webkit-box;-webkit-line-clamp:2;
                                          -webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $module->description }}
                                </p>
                            @endif

                            {{-- Nombre d'items --}}
                            <div style="font-size:.78rem;color:var(--text-muted);">
                                <i class="bi bi-card-list me-1"></i>
                                {{ $module->items_count }} item{{ $module->items_count > 1 ? 's' : '' }}
                                &nbsp;·&nbsp;
                                <i class="bi bi-clock me-1"></i>
                                {{ $module->created_at->diffForHumans() }}
                            </div>

                            {{-- Boutons d'action --}}
                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('modules.show', $module) }}"
                                   class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-eye me-1"></i>Ouvrir
                                </a>
                                <a href="{{ route('modules.edit', $module) }}"
                                   class="btn btn-sm"
                                   style="color:var(--text-muted);border:1px solid var(--card-border);">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                {{-- Bouton de suppression avec confirmation --}}
                                <form method="POST" action="{{ route('modules.destroy', $module) }}"
                                      onsubmit="return confirm('Supprimer ce module et tous ses items ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                            style="color:#ef4444;border:1px solid var(--card-border);">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination Bootstrap --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $modules->links('pagination::bootstrap-5') }}
        </div>
    @endif

</x-app-layout>
