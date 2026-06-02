<x-app-layout>
    <x-slot name="pageTitle">Nouveau module</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Nouveau</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-collection-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Créer un nouveau module</span>
                </div>
                <div class="card-body p-4">

                    {{-- Formulaire de création d'un nouveau module --}}
                    {{-- POST vers modules.store qui enregistre en base de données --}}
                    <form method="POST" action="{{ route('modules.store') }}">
                        @csrf

                        {{-- Titre du module (obligatoire) --}}
                        {{-- C'est le nom principal du module, limité à 255 caractères --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Titre du module <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}"
                                   placeholder="Ex : Fleurs du jardin, Oiseaux d'Europe…"
                                   autofocus>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description du module (facultatif) --}}
                        {{-- Peut contenir jusqu'à 1000 caractères pour une description détaillée --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                                <span style="color:var(--text-muted);font-weight:400;">(facultative)</span>
                            </label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Décrivez le contenu de ce module…">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Visibilité du module - Privé par défaut --}}
                        {{-- Si activé, le module apparaît dans la Bibliothèque publique --}}
                        {{-- Les autres utilisateurs peuvent voir et s'entraîner dessus --}}
                        <div class="mb-4">
                            <label class="form-label d-block">Visibilité</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_public"
                                       name="is_public"
                                       value="1"
                                       {{ old('is_public') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public"
                                       style="font-size:.875rem;color:var(--text-muted);">
                                    Rendre ce module public (visible par tous)
                                </label>
                            </div>
                        </div>

                        {{-- Boutons d'action du formulaire --}}
                        {{-- "Créer le module" envoie le formulaire --}}
                        {{-- "Annuler" redirection vers la liste des modules --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Créer le module
                            </button>
                            <a href="{{ route('modules.index') }}"
                               class="btn"
                               style="color:var(--text-muted);border:1px solid var(--card-border);">
                                Annuler
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
