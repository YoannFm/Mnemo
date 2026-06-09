<x-app-layout>
    <x-slot name="pageTitle">Modifier le module</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Modifier</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Modifier le module</span>
                </div>
                <div class="card-body p-4">

                    {{-- Formulaire de modification (méthode PUT via @method) --}}
                    <form method="POST" action="{{ route('modules.update', $module) }}">
                        @csrf
                        @method('PUT')

                        {{-- Titre --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Titre du module <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $module->title) }}"
                                   autofocus>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                                <span style="color:var(--text-muted);font-weight:400;">(facultative)</span>
                            </label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $module->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Visibilité --}}
                        <div class="mb-3">
                            <label class="form-label d-block">Visibilité</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_public"
                                       name="is_public"
                                       value="1"
                                       {{ old('is_public', $module->is_public) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public"
                                       style="font-size:.875rem;color:var(--text-muted);">
                                    Rendre ce module public (visible par tous)
                                </label>
                            </div>
                        </div>

                        {{-- Autoriser la duplication --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="allow_duplication"
                                       name="allow_duplication"
                                       value="1"
                                       {{ old('allow_duplication', $module->allow_duplication ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_duplication"
                                       style="font-size:.875rem;color:var(--text-muted);">
                                    Autoriser les autres utilisateurs à dupliquer ce module
                                </label>
                            </div>
                        </div>

                        {{-- Transfert de propriété --}}
                        <div class="mb-4 p-3" style="border:1px solid var(--card-border);border-radius:8px;">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                <i class="bi bi-person-check me-1" style="color:var(--accent);"></i>
                                Transférer la propriété
                            </label>
                            <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">
                                Entrez l'adresse email de l'utilisateur à qui transférer ce module. Vous n'en serez plus le propriétaire.
                            </p>
                            <input type="email"
                                   id="new_owner_email"
                                   name="new_owner_email"
                                   class="form-control @error('new_owner_email') is-invalid @enderror"
                                   placeholder="email@exemple.com"
                                   value="{{ old('new_owner_email') }}">
                            @error('new_owner_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tags (plugin) --}}
                        @includeIf('tags::field', ['module' => $module])

                        {{-- Boutons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('modules.show', $module) }}"
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
