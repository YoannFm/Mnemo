<x-app-layout>
    <x-slot name="pageTitle">Ajouter un item</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Ajouter un item</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-image-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Ajouter un item à « {{ $module->title }} »</span>
                </div>
                <div class="card-body p-4">

                    {{-- Formulaire d'ajout d'item dans le module --}}
                    {{-- enctype="multipart/form-data" obligatoire car on upload une photo --}}
                    <form method="POST"
                          action="{{ route('modules.items.store', $module) }}"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Nom français de l'item (obligatoire) --}}
                        {{-- Sera utilisé dans les questions Q1 et Q4 en français --}}
                        <div class="mb-3">
                            <label for="name_fr" class="form-label">
                                Nom <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text"
                                   id="name_fr"
                                   name="name_fr"
                                   class="form-control @error('name_fr') is-invalid @enderror"
                                   value="{{ old('name_fr') }}"
                                   placeholder="Ex : Marguerite"
                                   autofocus>
                            @error('name_fr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nom anglais de l'item (obligatoire) --}}
                        {{-- Sera utilisé dans la question Q4 (traduction anglaise) --}}
                        <div class="mb-3">
                            <label for="name_alt" class="form-label">
                                Traduction <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text"
                                   id="name_alt"
                                   name="name_alt"
                                   class="form-control @error('name_alt') is-invalid @enderror"
                                   value="{{ old('name_alt') }}"
                                   placeholder="Ex : Daisy">
                            @error('name_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fonction / Description de l'item (obligatoire) --}}
                        {{-- Sera utilisé dans la question Q2 et Q3 (description textuelle) --}}
                        {{-- Max 2000 caractères pour une description détaillée --}}
                        <div class="mb-3">
                            <label for="function_text" class="form-label">
                                Fonction / Description <span style="color:var(--text-muted);font-size:.8rem;font-weight:400;">(optionnel)</span>
                            </label>
                            <textarea id="function_text"
                                      name="function_text"
                                      class="form-control @error('function_text') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Décrivez la fonction ou les caractéristiques de cet élément…">{{ old('function_text') }}</textarea>
                            @error('function_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Upload de photo (obligatoire) --}}
                        {{-- La photo sera utilisée dans les questions Q1, Q3, Q5, Q8 --}}
                        <div class="mb-4">
                            <label for="photo" class="form-label">
                                Photo <span style="color:var(--text-muted);font-size:.8rem;font-weight:400;">(optionnel, max 2 Mo)</span>
                            </label>

                            {{-- Zone de prévisualisation de la photo avant upload --}}
                            {{-- Masquée par défaut, s'affiche quand l'utilisateur sélectionne une photo --}}
                            <div id="photo-preview"
                                 style="display:none;width:100%;height:180px;border-radius:10px;overflow:hidden;
                                        background:#0f1117;margin-bottom:.75rem;">
                                <img id="preview-img" src=""
                                     style="width:100%;height:100%;object-fit:cover;">
                            </div>

                            {{-- Input file pour la sélection de la photo --}}
                            {{-- required: la photo est obligatoire --}}
                            {{-- onchange appelle previewPhoto() pour montrer l'aperçu --}}
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
                                Formats acceptés : JPEG, PNG, WebP - La photo sera compressée automatiquement.
                            </div>
                        </div>

                        {{-- Boutons d'action du formulaire --}}
                        {{-- "Ajouter l'item" envoie le formulaire et enregistre l'item --}}
                        {{-- "Annuler" redirection vers la page du module --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter l'item
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
         * Prévisualise la photo sélectionnée avant l'upload.
         * Lit le fichier avec FileReader et l'injecte dans l'élément <img>.
         *
         * @param {HTMLInputElement} input - L'input file déclenché
         */
        function previewPhoto(input) {
            const preview = document.getElementById('photo-preview');
            const img     = document.getElementById('preview-img');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                // Aucun fichier sélectionné : on masque la prévisualisation
                preview.style.display = 'none';
            }
        }
    </script>

</x-app-layout>
