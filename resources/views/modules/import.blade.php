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

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

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

            {{-- Structure du ZIP --}}
            <div class="card mt-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open" style="color:var(--accent);"></i>
                    <span class="fw-semibold" style="font-size:.875rem;">Structure du fichier ZIP</span>
                </div>
                <div class="card-body p-3">
                    <pre style="background:var(--body-bg);border:1px solid var(--card-border);border-radius:4px;padding:.75rem 1rem;font-size:.78rem;color:var(--text-muted);margin:0;line-height:1.7;">
<span style="color:var(--accent);">mon-module.zip</span>
├── <span style="color:var(--accent);">manifest.yml</span>
└── images/
    ├── photo-item1.jpg
    └── photo-item2.png</pre>
                </div>
            </div>

            {{-- Structure du manifest --}}
            <div class="card mt-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-code" style="color:var(--accent);"></i>
                    <span class="fw-semibold" style="font-size:.875rem;">Structure du manifest.yml</span>
                </div>
                <div class="card-body p-3">
                    <pre style="background:var(--body-bg);border:1px solid var(--card-border);border-radius:4px;padding:.75rem 1rem;font-size:.78rem;color:var(--text-primary);margin:0;line-height:1.7;"><span style="color:var(--text-muted);">title:</span> <span style="color:var(--accent);">Nom du module</span>
<span style="color:var(--text-muted);">description:</span> Description optionnelle

<span style="color:var(--text-muted);">items:</span>
  - <span style="color:var(--text-muted);">name_fr:</span>       <span style="color:var(--accent);">Nom en français</span>     <span style="color:#6c757d;"># obligatoire</span>
    <span style="color:var(--text-muted);">name_en:</span>       <span style="color:var(--accent);">Nom en anglais</span>      <span style="color:#6c757d;"># obligatoire</span>
    <span style="color:var(--text-muted);">function_text:</span> Description / définition  <span style="color:#6c757d;"># optionnel</span>
    <span style="color:var(--text-muted);">image:</span>         images/photo.jpg  <span style="color:#6c757d;"># optionnel</span>
  - <span style="color:var(--text-muted);">name_fr:</span>       Autre élément
    <span style="color:var(--text-muted);">name_en:</span>       Another item
    <span style="color:var(--text-muted);">function_text:</span> <span style="color:#6c757d;">""</span>
    <span style="color:var(--text-muted);">image:</span>         <span style="color:#6c757d;">""</span></pre>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
