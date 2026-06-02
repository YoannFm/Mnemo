<x-app-layout>
    <x-slot name="pageTitle">{{ $module->title }}</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">{{ $module->title }}</li>
        </ol>
    </nav>

    {{-- ── En-tête du module ── --}}
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0">{{ $module->title }}</h4>
                @if ($module->is_public)
                    <span class="badge-public"><i class="bi bi-globe2 me-1"></i>Public</span>
                @else
                    <span class="badge-private"><i class="bi bi-lock me-1"></i>Privé</span>
                @endif
            </div>
            @if ($module->description)
                <p style="color:var(--text-muted);font-size:.875rem;margin:0;">{{ $module->description }}</p>
            @endif
            <div class="mt-2" style="font-size:.78rem;color:var(--text-muted);">
                <i class="bi bi-card-list me-1"></i>
                {{ $items->total() }} item{{ $items->total() > 1 ? 's' : '' }}
                &nbsp;·&nbsp;
                <i class="bi bi-clock me-1"></i>
                Créé {{ $module->created_at->diffForHumans() }}
            </div>
        </div>

        {{-- Actions sur le module (uniquement pour le propriétaire) --}}
        @if (Auth::id() === $module->owner_id)
            <div class="d-flex gap-2 flex-wrap">
                {{-- Bouton d'ajout d'item --}}
                <a href="{{ route('modules.items.create', $module) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter un item
                </a>
                {{-- Bouton d'import CSV en masse --}}
                <a href="{{ route('modules.items.import.form', $module) }}"
                   class="btn"
                   style="color:var(--accent);border:1px solid var(--accent);">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import CSV
                </a>
                <a href="{{ route('modules.edit', $module) }}"
                   class="btn"
                   style="color:var(--text-muted);border:1px solid var(--card-border);">
                    <i class="bi bi-pencil me-1"></i> Modifier
                </a>
                <form method="POST" action="{{ route('modules.destroy', $module) }}"
                      onsubmit="return confirm('Supprimer ce module et tous ses items ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn"
                            style="color:#ef4444;border:1px solid var(--card-border);">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- ── Boutons d'entraînement (Test / Anki) - nécessite au moins 4 items ── --}}
    @if ($items->total() >= 4)
        <div class="d-grid gap-2 mb-4" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <a href="{{ route('test.show', $module) }}" class="btn btn-primary" style="height:auto;padding:1rem;">
                <div style="font-size:1.5rem;margin-bottom:.25rem;">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div style="font-weight:600;font-size:.9rem;">Mode Test</div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.7);">Score final</div>
            </a>
            <a href="{{ route('anki.show', $module) }}" class="btn btn-outline-primary" style="height:auto;padding:1rem;border-width:2px;">
                <div style="font-size:1.5rem;margin-bottom:.25rem;">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div style="font-weight:600;font-size:.9rem;">Mode Anki</div>
                <div style="font-size:.75rem;color:var(--text-muted);">Infini</div>
            </a>
        </div>
    @elseif ($items->total() > 0 && $items->total() < 4)
        <div class="alert d-flex align-items-center gap-2 mb-4"
             style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);color:#fbbf24;border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span style="font-size:.875rem;">
                Il faut au moins <strong>4 items</strong> pour s'entraîner.
                Encore {{ 4 - $items->total() }} à ajouter !
            </span>
        </div>
    @endif

    {{-- ── Liste des items ── --}}
    @if ($items->isEmpty())
        {{-- État vide - aucun item dans le module --}}
        <div class="card text-center py-5">
            <i class="bi bi-image" style="font-size:3rem;color:var(--text-muted);"></i>
            <h6 class="mt-3">Aucun item dans ce module</h6>
            <p style="color:var(--text-muted);font-size:.875rem;">
                Ajoutez des éléments à mémoriser pour commencer à vous entraîner.
            </p>
            @if (Auth::id() === $module->owner_id)
                <a href="{{ route('modules.items.create', $module) }}"
                   class="btn btn-primary mx-auto" style="width:fit-content;">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter le premier item
                </a>
            @endif
        </div>
    @else
        <div class="row g-3">
            @foreach ($items as $item)
                {{-- Carte d'un item --}}
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card h-100">

                        {{-- Photo de l'item --}}
                        <div style="height:160px;overflow:hidden;border-radius:12px 12px 0 0;background:#0f1117;">
                            <img src="{{ $item->photo_url }}"
                                 alt="{{ $item->name_fr }}"
                                 style="width:100%;height:100%;object-fit:cover;opacity:.9;">
                        </div>

                        <div class="card-body p-3" style="gap:.5rem;display:flex;flex-direction:column;">

                            {{-- Noms FR / EN --}}
                            <div>
                                <div class="fw-semibold" style="font-size:.95rem;">{{ $item->name_fr }}</div>
                                <div style="font-size:.8rem;color:var(--accent);">{{ $item->name_en }}</div>
                            </div>

                            {{-- Fonction/Description --}}
                            <p style="font-size:.78rem;color:var(--text-muted);margin:0;
                                      display:-webkit-box;-webkit-line-clamp:3;
                                      -webkit-box-orient:vertical;overflow:hidden;">
                                {{ $item->function_text }}
                            </p>

                            {{-- Boutons d'action (propriétaire uniquement) --}}
                            @if (Auth::id() === $module->owner_id)
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="{{ route('modules.items.edit', [$module, $item]) }}"
                                       class="btn btn-sm flex-grow-1"
                                       style="color:var(--text-muted);border:1px solid var(--card-border);">
                                        <i class="bi bi-pencil me-1"></i>Modifier
                                    </a>
                                    <form method="POST"
                                          action="{{ route('modules.items.destroy', [$module, $item]) }}"
                                          onsubmit="return confirm('Supprimer cet item ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm"
                                                style="color:#ef4444;border:1px solid var(--card-border);">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    @endif

</x-app-layout>
