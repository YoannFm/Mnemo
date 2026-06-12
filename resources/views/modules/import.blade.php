<x-app-layout>
    <x-slot name="pageTitle">Importer un module</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Importer un module</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-zip-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Importer un module depuis un fichier ZIP</span>
                </div>
                <div class="card-body p-4">

                    <p style="color:var(--text-muted);font-size:.875rem;">
                        Sélectionnez un fichier <code>.zip</code> exporté depuis Mnemo.
                        Le module sera créé dans votre espace personnel (privé par défaut).
                    </p>

                    <form method="POST"
                          action="{{ route('modules.import') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="zip_file" class="form-label">
                                Fichier ZIP <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="file"
                                   id="zip_file"
                                   name="zip_file"
                                   class="form-control @error('zip_file') is-invalid @enderror"
                                   accept=".zip"
                                   required>
                            @error('zip_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem;">
                                Format accepté : ZIP - Taille max : 50 Mo
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i> Importer
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

            {{-- Info format --}}
            <div class="card mt-3">
                <div class="card-body p-3">
                    <p class="mb-1 fw-semibold" style="font-size:.85rem;">
                        <i class="bi bi-info-circle me-1" style="color:var(--accent);"></i>
                        Format du ZIP
                    </p>
                    <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
                        Le ZIP doit contenir un fichier <code>manifest.yml</code> à la racine
                        et un dossier <code>images/</code> pour les photos.
                        Ce format est produit automatiquement par l'export de module Mnemo.
                    </p>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
