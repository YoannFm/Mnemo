<x-app-layout>
    <x-slot name="pageTitle">Modifier l'item</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Modifier « {{ $item->name_fr }} »</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Modifier l'item</span>
                </div>
                <div class="card-body p-4">

                    {{-- Formulaire de modification --}}
                    <form method="POST"
                          action="{{ route('modules.items.update', [$module, $item]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Nom français --}}
                        <div class="mb-3">
                            <label for="name_fr" class="form-label">
                                Nom français <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text"
                                   id="name_fr"
                                   name="name_fr"
                                   class="form-control @error('name_fr') is-invalid @enderror"
                                   value="{{ old('name_fr', $item->name_fr) }}"
                                   autofocus>
                            @error('name_fr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nom anglais --}}
                        <div class="mb-3">
                            <label for="name_en" class="form-label">
                                Nom anglais <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text"
                                   id="name_en"
                                   name="name_en"
                                   class="form-control @error('name_en') is-invalid @enderror"
                                   value="{{ old('name_en', $item->name_en) }}">
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fonction / Description --}}
                        <div class="mb-3">
                            <label for="function_text" class="form-label">
                                Fonction / Description <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea id="function_text"
                                      name="function_text"
                                      class="form-control @error('function_text') is-invalid @enderror"
                                      rows="4">{{ old('function_text', $item->function_text) }}</textarea>
                            @error('function_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Photo actuelle + possibilité de remplacement --}}
                        <div class="mb-4">
                            <label class="form-label">Photo</label>

                            {{-- Aperçu de la photo actuelle --}}
                            @if ($item->photo_path)
                                <div class="mb-2">
                                    <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.4rem;">
                                        Photo actuelle :
                                    </p>
                                    <img src="{{ $item->photo_url }}"
                                         alt="{{ $item->name_fr }}"
                                         id="preview-img"
                                         style="width:100%;max-height:180px;object-fit:cover;
                                                border-radius:10px;background:#0f1117;">
                                </div>
                            @else
                                <div id="photo-preview" style="display:none;margin-bottom:.75rem;">
                                    <img id="preview-img" src=""
                                         style="width:100%;max-height:180px;object-fit:cover;border-radius:10px;">
                                </div>
                            @endif

                            <input type="file"
                                   id="photo"
                                   name="photo"
                                   class="form-control @error('photo') is-invalid @enderror"
                                   accept="image/*"
                                   onchange="previewPhoto(this)">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem;">
                                Laisser vide pour conserver la photo actuelle.
                            </div>
                        </div>

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

    <script>
        /**
         * Prévisualise la nouvelle photo sélectionnée.
         * Remplace l'image actuelle dans la prévisualisation.
         *
         * @param {HTMLInputElement} input - L'input file déclenché
         */
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const img = document.getElementById('preview-img');
                    if (img) {
                        img.src = e.target.result;
                    }

                    // Si la div de prévisualisation existe (pas de photo actuelle), on l'affiche
                    const preview = document.getElementById('photo-preview');
                    if (preview) {
                        preview.style.display = 'block';
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</x-app-layout>
