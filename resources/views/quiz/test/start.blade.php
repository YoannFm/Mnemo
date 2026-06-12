<x-app-layout>
    <x-slot name="pageTitle">Mode Test - {{ $module->title }}</x-slot>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Test</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">

            <div class="mb-4">
                <h4 class="mb-1">Mode Test</h4>
                <p style="color:var(--text-muted);font-size:.875rem;">
                    Répondez à un nombre fixe de questions pour obtenir un score final.
                </p>
            </div>

            <form method="POST" action="{{ route('test.start', $module) }}" id="test-form">
                @csrf
                <input type="hidden" name="mode" id="mode-value" value="random">

                {{-- Nombre de questions --}}
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <label for="question_count" class="form-label fw-semibold" style="font-size:.85rem;">
                            <i class="bi bi-list-ol me-1" style="color:var(--accent);"></i>Nombre de questions
                        </label>
                        <input type="number"
                               id="question_count"
                               name="question_count"
                               class="form-control @error('question_count') is-invalid @enderror"
                               value="{{ old('question_count', min(10, $module->items()->count())) }}"
                               min="1"
                               max="{{ $module->items()->count() }}"
                               required>
                        @error('question_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem;">
                            {{ $module->items()->count() }} items disponibles - entre 1 et {{ $module->items()->count() }} questions.
                        </div>

                        <label class="form-label fw-semibold mt-3" style="font-size:.85rem;">
                            <i class="bi bi-ui-checks me-1" style="color:var(--accent);"></i>Nombre de réponses proposées
                        </label>
                        <select name="option_count" class="form-select">
                            @foreach([2,3,4,5,6,7,8] as $n)
                                <option value="{{ $n }}" {{ $n === 4 ? 'selected' : '' }}>{{ $n }} réponses</option>
                            @endforeach
                        </select>
                        <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem;">
                            Si le module n'a pas assez d'items, certaines réponses seront répétées.
                        </div>
                    </div>
                </div>

                {{-- Mode aléatoire --}}
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2" style="padding-left:0;">
                            <input class="form-check-input" type="checkbox" id="random-toggle" checked style="width:2.5rem;height:1.25rem;cursor:pointer;margin:0;">
                            <label class="form-check-label fw-semibold" for="random-toggle" style="cursor:pointer;">
                                <i class="bi bi-shuffle me-1" style="color:var(--accent);"></i> Aléatoire
                            </label>
                        </div>
                        <p class="mb-0 mt-1" style="font-size:.8rem;color:var(--text-muted);">Tous les types de questions mélangés.</p>
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
                                @if($module->field_photo)
                                    <option value="photo_path">Photo</option>
                                @endif
                                @if($module->field_audio)
                                    <option value="audio_path">Son</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div id="invalid-combo" class="alert alert-warning d-none py-2" style="font-size:.85rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i> L'entrée et la sortie doivent être différentes.
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('modules.show', $module) }}" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1" id="start-btn">
                        <i class="bi bi-play-fill me-1"></i> Commencer le test
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
    var modeMap = {
        'photo_path|name_fr':       'photo_to_name_fr',
        'photo_path|name_alt':      'photo_to_name_alt',
        'photo_path|function_text': 'photo_to_function',
        'audio_path|name_fr':       'audio_to_name_fr',
        'audio_path|name_alt':      'audio_to_name_alt',
        'name_fr|photo_path':       'name_fr_to_photo',
        'name_fr|name_alt':         'name_fr_to_name_alt',
        'name_fr|function_text':    'name_fr_to_function',
        'name_fr|audio_path':       'name_fr_to_audio',
        'name_alt|photo_path':      'name_alt_to_photo',
        'name_alt|name_fr':         'name_alt_to_name_fr',
        'name_alt|function_text':   'name_alt_to_function',
        'name_alt|audio_path':      'name_alt_to_audio',
        'function_text|photo_path': 'function_to_photo',
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
