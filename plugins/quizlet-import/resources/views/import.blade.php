<x-app-layout>
    <x-slot name="pageTitle">Import Quizlet</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Instructions --}}
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3" style="color:var(--text-primary);font-size:1rem;">
                        <i class="bi bi-info-circle me-2" style="color:var(--accent);"></i>Comment exporter depuis Quizlet
                    </h6>
                    <div style="font-size:.875rem;color:var(--text-muted);line-height:1.8;">
                        <strong style="color:var(--text-primary);">Si le set t'appartient :</strong><br>
                        Clique sur <strong>···</strong> → <strong>Exporter</strong> → copie le texte et colle-le ci-dessous.
                        <br><br>
                        <strong style="color:var(--text-primary);">Si le set appartient à quelqu'un d'autre :</strong><br>
                        Clique sur <strong>···</strong> → <strong>Faire une copie</strong> → ouvre ta copie → <strong>···</strong> → <strong>Exporter</strong> → copie le texte.
                    </div>
                </div>
            </div>

            {{-- Formulaire --}}
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('quizlet-import.import') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="module_name" style="font-weight:500;font-size:.875rem;color:var(--text-primary);display:block;margin-bottom:.4rem;">
                                Nom du module
                            </label>
                            <input type="text"
                                   class="form-control @error('module_name') is-invalid @enderror"
                                   id="module_name" name="module_name"
                                   value="{{ old('module_name') }}"
                                   placeholder="Ex : Vocabulaire biologie chapitre 3"
                                   style="background:var(--card-bg);border-color:var(--card-border);color:var(--text-primary);"
                                   required>
                            @error('module_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="separator" style="font-weight:500;font-size:.875rem;color:var(--text-primary);display:block;margin-bottom:.4rem;">
                                Séparateur
                            </label>
                            <select class="form-select" id="separator" name="separator"
                                    style="background:var(--card-bg);border-color:var(--card-border);color:var(--text-primary);"
                                    onchange="updatePreview()">
                                <option value="tab" {{ old('separator','tab')==='tab'?'selected':'' }}>Tabulation — défaut Quizlet</option>
                                <option value="semicolon" {{ old('separator')==='semicolon'?'selected':'' }}>Point-virgule ( ; )</option>
                                <option value="pipe" {{ old('separator')==='pipe'?'selected':'' }}>Pipe ( | )</option>
                            </select>
                            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem;">
                                Le séparateur est détecté automatiquement si tu laisses sur Tabulation.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" style="font-weight:500;font-size:.875rem;color:var(--text-primary);display:block;margin-bottom:.4rem;">
                                Texte exporté depuis Quizlet
                            </label>
                            <textarea class="form-control font-monospace @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="10"
                                      placeholder="terme1    définition1&#10;terme2    définition2&#10;..."
                                      style="background:var(--card-bg);border-color:var(--card-border);color:var(--text-primary);font-size:.82rem;"
                                      oninput="updatePreview()" required>{{ old('content') }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem;">
                                Une ligne = un item. Terme <em>séparateur</em> Définition.
                            </div>
                        </div>

                        {{-- Aperçu --}}
                        <div id="preview-section" style="display:none;margin-bottom:1.25rem;">
                            <div style="font-size:.875rem;font-weight:500;color:var(--text-primary);margin-bottom:.5rem;">
                                Aperçu — <span id="preview-count">0</span> items détectés
                            </div>
                            <div style="max-height:180px;overflow-y:auto;border:1px solid var(--card-border);border-radius:8px;">
                                <table class="table table-sm mb-0" style="font-size:.8rem;">
                                    <thead>
                                        <tr style="color:var(--text-muted);">
                                            <th style="width:45%;padding:.4rem .75rem;">Terme</th>
                                            <th style="padding:.4rem .75rem;">Définition</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-body"></tbody>
                                </table>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-2"></i>Créer le module
                        </button>

                    </form>
                </div>
            </div>

        </div>
    </div>

<script>
function getSep() {
    const v = document.getElementById('separator').value;
    return v === 'tab' ? '\t' : v === 'semicolon' ? ';' : '|';
}

function updatePreview() {
    const raw = document.getElementById('content').value.trim();
    const sep = getSep();
    const body = document.getElementById('preview-body');
    const section = document.getElementById('preview-section');
    const count = document.getElementById('preview-count');

    if (!raw) { section.style.display = 'none'; return; }

    const items = [];
    for (let line of raw.split(/\r?\n/)) {
        line = line.trim();
        if (!line) continue;
        const idx = line.indexOf(sep);
        if (idx === -1) continue;
        items.push([line.substring(0, idx).trim(), line.substring(idx + sep.length).trim()]);
    }

    if (!items.length) { section.style.display = 'none'; return; }

    count.textContent = items.length;
    body.innerHTML = items.slice(0, 10).map(([t, d]) =>
        `<tr><td style="padding:.35rem .75rem;color:var(--text-primary);">${esc(t)}</td><td style="padding:.35rem .75rem;color:var(--text-muted);">${esc(d)}</td></tr>`
    ).join('') + (items.length > 10 ? `<tr><td colspan="2" style="text-align:center;color:var(--text-muted);font-style:italic;padding:.35rem;">… et ${items.length - 10} autres</td></tr>` : '');
    section.style.display = '';
}

function esc(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('content').value.trim()) updatePreview();
});
</script>
</x-app-layout>
