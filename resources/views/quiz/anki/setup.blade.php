<x-app-layout>
    <x-slot name="pageTitle">Mode Anki - {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">

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
                    Mémorisation par répétition espacée sur <strong>{{ $module->title }}</strong>.
                </p>
            </div>

            {{-- Stats de progression --}}
            <div class="row g-2 mb-4 text-center">
                <div class="col-4">
                    <div class="card h-100 py-3">
                        <div class="fs-4 fw-bold" style="color:var(--accent);">{{ $dueCount }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">A réviser</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card h-100 py-3">
                        <div class="fs-4 fw-bold text-success">{{ $masteredCount }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Maîtrisés</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card h-100 py-3">
                        <div class="fs-4 fw-bold">{{ $totalCount }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">Total</div>
                    </div>
                </div>
            </div>

            {{-- Section Reprendre si session existante --}}
            @if($ankiSession)
            @php
                $modeLabels = [
                    'random'               => 'Aléatoire',
                    'photo_to_name_fr'     => 'Photo -> Nom',
                    'photo_to_name_alt'    => 'Photo -> Traduction',
                    'photo_to_function'    => 'Photo -> Description',
                    'function_to_photo'    => 'Description -> Photo',
                    'function_to_name_fr'  => 'Description -> Nom',
                    'function_to_name_alt' => 'Description -> Traduction',
                    'name_fr_to_name_alt'  => 'Nom -> Traduction',
                    'name_fr_to_photo'     => 'Nom -> Photo',
                    'name_fr_to_function'  => 'Nom -> Description',
                    'name_alt_to_photo'    => 'Traduction -> Photo',
                    'name_alt_to_function' => 'Traduction -> Description',
                    'name_alt_to_name_fr'  => 'Traduction -> Nom',
                    'audio_to_name_fr'     => 'Son -> Nom',
                    'audio_to_name_alt'    => 'Son -> Traduction',
                    'name_fr_to_audio'     => 'Nom -> Son',
                    'name_alt_to_audio'    => 'Traduction -> Son',
                ];
                $modeLabel = $modeLabels[$ankiSession->mode] ?? $ankiSession->mode;
            @endphp
            <div class="card mb-4" style="border:1px solid var(--accent);">
                <div class="card-body p-3">
                    <div class="fw-semibold mb-1" style="color:var(--accent);">
                        <i class="bi bi-arrow-clockwise me-1"></i> Session en pause
                    </div>
                    <p class="mb-1" style="font-size:.85rem;color:var(--text-muted);">
                        Mode : <strong>{{ $modeLabel }}</strong>
                        @if($ankiSession->isLearnMode() && $ankiSession->learn_total)
                            &middot; {{ count($ankiSession->learn_remaining ?? []) }} / {{ $ankiSession->learn_total }} items restants
                        @endif
                    </p>
                    <p class="mb-2" style="font-size:.8rem;color:var(--text-muted);">
                        Derniere activite : {{ $ankiSession->updated_at->diffForHumans() }}
                    </p>
                    <a href="{{ route('anki.question', $module) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-play-fill me-1"></i>Reprendre
                    </a>
                </div>
            </div>
            @endif

            {{-- Formulaire nouvelle session --}}
            <div class="mb-2">
                <h6 class="fw-semibold" style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">
                    {{ $ankiSession ? 'Nouvelle session' : 'Démarrer' }}
                </h6>
            </div>

            <form method="POST" action="{{ route('anki.start', $module) }}" id="anki-form">
                @csrf
                <input type="hidden" name="mode" id="mode-value" value="random">

                {{-- Mode aléatoire --}}
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2" style="padding-left:0;">
                            <input class="form-check-input" type="checkbox" id="random-toggle" checked style="width:2.5rem;height:1.25rem;cursor:pointer;margin:0;">
                            <label class="form-check-label fw-semibold" for="random-toggle" style="cursor:pointer;">
                                <i class="bi bi-shuffle me-1" style="color:var(--accent);"></i> Aléatoire
                            </label>
                        </div>
                        <p class="mb-0 mt-1" style="font-size:.8rem;color:var(--text-muted);">Tous les types de questions mélangés, répétition espacée SM-2.</p>
                    </div>
                </div>

                {{-- Choix entrée / sortie --}}
                <div id="io-section">
                    <h6 class="mb-3" style="color:var(--text-muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;">Entrée - Sortie</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                <i class="bi bi-eye me-1" style="color:var(--accent);"></i>Ce que je vois
                            </label>
                            <select id="input-field" class="form-select">
                                @if($module->field_photo)
                                    <option value="photo_path">Photo</option>
                                @endif
                                @if($module->field_audio)
                                    <option value="audio_path">Son</option>
                                @endif
                                @if($module->field_name_fr)
                                    <option value="name_fr">Nom</option>
                                @endif
                                @if($module->field_name_alt)
                                    <option value="name_alt">Traduction</option>
                                @endif
                                @if($module->field_function)
                                    <option value="function_text">Description</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                <i class="bi bi-pencil me-1" style="color:var(--accent);"></i>Ce que je réponds
                            </label>
                            <select id="output-field" class="form-select">
                                @if($module->field_name_fr)
                                    <option value="name_fr">Nom</option>
                                @endif
                                @if($module->field_name_alt)
                                    <option value="name_alt">Traduction</option>
                                @endif
                                @if($module->field_function)
                                    <option value="function_text">Description</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div id="invalid-combo" class="alert alert-warning d-none py-2" style="font-size:.85rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i> L'entrée et la sortie doivent être différentes.
                    </div>

                    <div class="card mb-2" style="border:1px solid var(--card-border);">
                        <div class="card-body p-3">
                            <div class="fw-semibold mb-1" style="font-size:.85rem;color:var(--accent);">
                                <i class="bi bi-mortarboard me-1"></i> Mode apprentissage actif
                            </div>
                            <p class="mb-0" style="font-size:.78rem;color:var(--text-muted);">
                                Chaque item est posé une fois. Réponse juste = retiré de la liste. Réponse fausse = remis dans le pool.
                                La session se termine quand tous les items sont réussis.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 align-items-center flex-wrap">
                    <a href="{{ route('modules.show', $module) }}" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary" id="start-btn">
                        <i class="bi bi-play-fill me-1"></i>
                        {{ $ankiSession ? 'Recommencer' : 'Commencer' }}
                    </button>
                </div>
            </form>

            {{-- Reset progression (hors du formulaire principal pour eviter l'imbrication) --}}
            @if($newCount < $totalCount)
            <form method="POST" action="{{ route('modules.progress.reset', $module) }}" class="mt-3"
                  onsubmit="return confirm('Réinitialiser toute votre progression sur ce module ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm" style="color:var(--danger, #dc3545);border:1px solid var(--danger, #dc3545);">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Remettre à zéro
                </button>
            </form>
            @endif

        </div>
    </div>

    <script>
    var modeMap = {
        'photo_path|name_fr':       'photo_to_name_fr',
        'photo_path|name_alt':      'photo_to_name_alt',
        'photo_path|function_text': 'photo_to_function',
        'audio_path|name_fr':       'audio_to_name_fr',
        'audio_path|name_alt':      'audio_to_name_alt',
        'name_fr|name_alt':         'name_fr_to_name_alt',
        'name_fr|function_text':    'name_fr_to_function',
        'name_alt|name_fr':         'name_alt_to_name_fr',
        'name_alt|function_text':   'name_alt_to_function',
        'function_text|name_fr':    'function_to_name_fr',
        'function_text|name_alt':   'function_to_name_alt',
    };

    var randomToggle = document.getElementById('random-toggle');
    var ioSection    = document.getElementById('io-section');
    var inputField   = document.getElementById('input-field');
    var outputField  = document.getElementById('output-field');
    var modeValue    = document.getElementById('mode-value');
    var startBtn     = document.getElementById('start-btn');
    var invalidCombo = document.getElementById('invalid-combo');

    function updateMode() {
        if (randomToggle.checked) {
            ioSection.style.opacity = '.4';
            ioSection.style.pointerEvents = 'none';
            modeValue.value = 'random';
            startBtn.disabled = false;
            return;
        }
        ioSection.style.opacity = '1';
        ioSection.style.pointerEvents = '';
        var inp = inputField.value;
        var out = outputField.value;
        if (inp === out) {
            invalidCombo.classList.remove('d-none');
            startBtn.disabled = true;
            modeValue.value = '';
        } else {
            invalidCombo.classList.add('d-none');
            startBtn.disabled = false;
            modeValue.value = modeMap[inp + '|' + out] || 'random';
        }
    }

    randomToggle.addEventListener('change', updateMode);
    inputField.addEventListener('change', updateMode);
    outputField.addEventListener('change', updateMode);
    updateMode();
    </script>

</x-app-layout>
