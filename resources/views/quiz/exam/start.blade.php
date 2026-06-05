<x-app-layout>
    <x-slot name="pageTitle">Mode Examen - {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb" style="font-size:.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a></li>
                    <li class="breadcrumb-item active" style="color:var(--text-muted);">Examen</li>
                </ol>
            </nav>

            <div class="mb-4">
                <h4 class="mb-1"><i class="bi bi-pencil-square me-2" style="color:var(--accent);"></i>Mode Examen</h4>
                <p style="color:var(--text-muted);font-size:.875rem;">
                    Tous les <strong>{{ $itemCount }} items</strong> du module seront testés en une seule session. Aucun feedback pendant l'examen. Le score s'affiche à la fin.
                </p>
            </div>

            <form method="POST" action="{{ route('exam.start', $module) }}" id="exam-form">
                @csrf
                <input type="hidden" name="mode" id="mode-value" value="random">

                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2 mb-3" style="padding-left:0;">
                            <input class="form-check-input" type="checkbox" id="random-toggle" checked style="width:2.5rem;height:1.25rem;cursor:pointer;margin:0;">
                            <label class="form-check-label fw-semibold" for="random-toggle" style="cursor:pointer;">
                                <i class="bi bi-shuffle me-1" style="color:var(--accent);"></i> Aléatoire
                            </label>
                        </div>

                        <div id="io-section">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label" style="font-size:.85rem;">Ce que je vois</label>
                                    <select id="input-field" class="form-select form-select-sm">
                                        <option value="photo_path">Photo</option>
                                        <option value="name_fr">Nom</option>
                                        <option value="name_en">Traduction</option>
                                        <option value="function_text">Description</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" style="font-size:.85rem;">Ce que je réponds</label>
                                    <select id="output-field" class="form-select form-select-sm">
                                        <option value="name_fr">Nom</option>
                                        <option value="name_en">Traduction</option>
                                        <option value="function_text">Description</option>
                                        <option value="photo_path">Photo</option>
                                    </select>
                                </div>
                            </div>
                            <div id="invalid-combo" class="alert alert-warning d-none mt-2 py-2" style="font-size:.85rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i> L'entrée et la sortie doivent être différentes.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('modules.show', $module) }}" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">Annuler</a>
                    <button type="submit" class="btn btn-primary" id="start-btn">
                        <i class="bi bi-pencil-square me-1"></i>Commencer l'examen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    var modeMap = {
        'photo_path|name_fr': 'photo_to_name_fr',
        'photo_path|name_en': 'photo_to_name_en',
        'photo_path|function_text': 'photo_to_function',
        'name_fr|photo_path': 'name_fr_to_photo',
        'name_fr|name_en': 'name_fr_to_name_en',
        'name_fr|function_text': 'name_fr_to_function',
        'name_en|photo_path': 'name_en_to_photo',
        'name_en|name_fr': 'name_en_to_name_fr',
        'name_en|function_text': 'name_en_to_function',
        'function_text|photo_path': 'function_to_photo',
        'function_text|name_fr': 'function_to_name_fr',
        'function_text|name_en': 'function_to_name_en',
    };
    var randomToggle = document.getElementById('random-toggle');
    var ioSection = document.getElementById('io-section');
    var inputField = document.getElementById('input-field');
    var outputField = document.getElementById('output-field');
    var modeValue = document.getElementById('mode-value');
    var startBtn = document.getElementById('start-btn');
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
        var inp = inputField.value, out = outputField.value;
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
