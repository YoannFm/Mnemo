<x-app-layout>
    <x-slot name="pageTitle">Mode Anki - {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb" style="font-size:.85rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:var(--text-muted);">Anki</li>
                </ol>
            </nav>

            <div class="mb-4">
                <h4 class="mb-1">Mode Anki</h4>
                <p style="color:var(--text-muted);font-size:.875rem;">
                    Choisissez comment vous voulez vous entraîner sur <strong>{{ $module->title }}</strong>.
                </p>
            </div>

            <form method="POST" action="{{ route('anki.start', $module) }}">
                @csrf

                {{-- Mode aléatoire --}}
                <div class="card mb-3" style="cursor:pointer;" onclick="selectMode('random')">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <input type="radio" name="mode" id="mode_random" value="random" class="form-check-input mt-0" checked style="width:1.25rem;height:1.25rem;cursor:pointer;">
                        <label for="mode_random" style="cursor:pointer;flex:1;margin:0;">
                            <div class="fw-semibold"><i class="bi bi-shuffle me-2" style="color:var(--accent);"></i>Aléatoire</div>
                            <div style="font-size:.8rem;color:var(--text-muted);">Tous les types de questions mélangés</div>
                        </label>
                    </div>
                </div>

                {{-- Modes entrée/sortie --}}
                <h6 class="mb-2 mt-4" style="color:var(--text-muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;">Entrée - Sortie</h6>

                <h6 class="mb-2" style="color:var(--text-muted);font-size:.75rem;font-weight:600;margin-top:1.5rem;">Image en entrée</h6>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('photo_to_name_fr')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_photo_name_fr" value="photo_to_name_fr" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_photo_name_fr" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-image me-1" style="color:var(--accent);"></i>Image
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Nom FR
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('photo_to_name_en')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_photo_name_en" value="photo_to_name_en" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_photo_name_en" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-image me-1" style="color:var(--accent);"></i>Image
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Nom EN
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('photo_to_function')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_photo_function" value="photo_to_function" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_photo_function" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-image me-1" style="color:var(--accent);"></i>Image
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Description
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-2" style="color:var(--text-muted);font-size:.75rem;font-weight:600;margin-top:1.5rem;">Description en entrée</h6>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('function_to_photo')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_function_photo" value="function_to_photo" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_function_photo" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-card-text me-1" style="color:var(--accent);"></i>Description
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Image
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('function_to_name_fr')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_function_name_fr" value="function_to_name_fr" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_function_name_fr" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-card-text me-1" style="color:var(--accent);"></i>Description
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Nom FR
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('function_to_name_en')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_function_name_en" value="function_to_name_en" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_function_name_en" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-card-text me-1" style="color:var(--accent);"></i>Description
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Nom EN
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-2" style="color:var(--text-muted);font-size:.75rem;font-weight:600;margin-top:1.5rem;">Noms en entrée</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('name_fr_to_name_en')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_fr_en" value="name_fr_to_name_en" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_fr_en" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-translate me-1" style="color:var(--accent);"></i>Nom FR
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Nom EN
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('name_fr_to_photo')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_fr_photo" value="name_fr_to_photo" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_fr_photo" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-translate me-1" style="color:var(--accent);"></i>Nom FR
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Image
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('name_fr_to_function')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_fr_function" value="name_fr_to_function" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_fr_function" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-translate me-1" style="color:var(--accent);"></i>Nom FR
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Description
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('name_en_to_photo')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_en_photo" value="name_en_to_photo" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_en_photo" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-translate me-1" style="color:var(--accent);"></i>Nom EN
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Image
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('name_en_to_function')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_en_function" value="name_en_to_function" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_en_function" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-translate me-1" style="color:var(--accent);"></i>Nom EN
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Description
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card" style="cursor:pointer;" onclick="selectMode('name_en_to_name_fr')">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <input type="radio" name="mode" id="mode_en_fr" value="name_en_to_name_fr" class="form-check-input mt-0" style="width:1.25rem;height:1.25rem;cursor:pointer;">
                                <label for="mode_en_fr" style="cursor:pointer;flex:1;margin:0;">
                                    <div class="fw-semibold" style="font-size:.9rem;">
                                        <i class="bi bi-translate me-1" style="color:var(--accent);"></i>Nom EN
                                        <i class="bi bi-arrow-right mx-1" style="color:var(--text-muted);"></i>
                                        Nom FR
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('modules.show', $module) }}" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-fill me-1"></i>Commencer
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function selectMode(value) {
            document.querySelector(`input[value="${value}"]`).checked = true;
        }
    </script>

</x-app-layout>
