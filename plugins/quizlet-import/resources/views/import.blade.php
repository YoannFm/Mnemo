<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-5 mb-0">Import Quizlet</h2>
    </x-slot>

    <div class="container py-4" style="max-width: 760px;">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Instructions --}}
        <div class="card mb-4" style="background: var(--card-bg, #fff); border-color: var(--card-border, #dee2e6);">
            <div class="card-body">
                <h5 class="card-title" style="color: var(--text-primary, inherit);">
                    <i class="bi bi-info-circle me-2"></i>Comment exporter depuis Quizlet
                </h5>
                <ol class="mb-0" style="color: var(--text-muted, #6c757d);">
                    <li>Ouvrez votre set dans Quizlet.</li>
                    <li>Cliquez sur le menu <strong>···</strong> (trois points).</li>
                    <li>Choisissez <strong>Exporter</strong>.</li>
                    <li>Copiez le texte affiché et collez-le ci-dessous.</li>
                </ol>
            </div>
        </div>

        {{-- Import Form --}}
        <div class="card" style="background: var(--card-bg, #fff); border-color: var(--card-border, #dee2e6);">
            <div class="card-body">
                <form method="POST" action="{{ route('quizlet-import.import') }}" id="quizlet-form">
                    @csrf

                    {{-- Module name --}}
                    <div class="mb-3">
                        <label for="module_name" class="form-label fw-semibold" style="color: var(--text-primary, inherit);">
                            Nom du module
                        </label>
                        <input
                            type="text"
                            class="form-control @error('module_name') is-invalid @enderror"
                            id="module_name"
                            name="module_name"
                            value="{{ old('module_name') }}"
                            placeholder="Ex : Vocabulaire biologie chapitre 3"
                            required
                        >
                        @error('module_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Separator --}}
                    <div class="mb-3">
                        <label for="separator" class="form-label fw-semibold" style="color: var(--text-primary, inherit);">
                            Séparateur entre terme et définition
                        </label>
                        <select class="form-select @error('separator') is-invalid @enderror" id="separator" name="separator">
                            <option value="tab" {{ old('separator', 'tab') === 'tab' ? 'selected' : '' }}>
                                Tabulation (défaut Quizlet)
                            </option>
                            <option value="semicolon" {{ old('separator') === 'semicolon' ? 'selected' : '' }}>
                                Point-virgule ( ; )
                            </option>
                            <option value="pipe" {{ old('separator') === 'pipe' ? 'selected' : '' }}>
                                Pipe ( | )
                            </option>
                        </select>
                        @error('separator')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Content textarea --}}
                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold" style="color: var(--text-primary, inherit);">
                            Contenu exporté depuis Quizlet
                        </label>
                        <textarea
                            class="form-control font-monospace @error('content') is-invalid @enderror"
                            id="content"
                            name="content"
                            rows="12"
                            placeholder="terme1&#9;définition1&#10;terme2&#9;définition2&#10;..."
                            required
                            oninput="updatePreview()"
                        >{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" style="color: var(--text-muted, #6c757d);">
                            Une ligne = un item. Chaque ligne doit contenir terme <em>séparateur</em> définition.
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="mb-4" id="preview-section" style="display:none;">
                        <p class="fw-semibold mb-2" style="color: var(--text-primary, inherit);">
                            Aperçu (<span id="preview-count">0</span> items détectés)
                        </p>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:45%; color: var(--text-primary, inherit);">Terme (name_fr)</th>
                                        <th style="color: var(--text-primary, inherit);">Définition (function_text)</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-body"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn px-4" style="background: var(--accent, #0d6efd); color: #fff; border: none;">
                            <i class="bi bi-upload me-2"></i>Créer le module
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function getSeparator() {
            const val = document.getElementById('separator').value;
            if (val === 'tab') return '\t';
            if (val === 'semicolon') return ';';
            if (val === 'pipe') return '|';
            return '\t';
        }

        function updatePreview() {
            const content = document.getElementById('content').value.trim();
            const sep = getSeparator();
            const previewSection = document.getElementById('preview-section');
            const previewBody = document.getElementById('preview-body');
            const previewCount = document.getElementById('preview-count');

            if (!content) {
                previewSection.style.display = 'none';
                return;
            }

            const lines = content.split(/\r?\n/);
            const items = [];
            for (let line of lines) {
                line = line.trim();
                if (!line) continue;
                const idx = line.indexOf(sep);
                if (idx === -1) continue;
                items.push([line.substring(0, idx).trim(), line.substring(idx + sep.length).trim()]);
            }

            if (items.length === 0) {
                previewSection.style.display = 'none';
                return;
            }

            previewCount.textContent = items.length;
            previewBody.innerHTML = items.slice(0, 10).map(([term, def]) =>
                `<tr><td>${escapeHtml(term)}</td><td>${escapeHtml(def)}</td></tr>`
            ).join('');

            if (items.length > 10) {
                previewBody.innerHTML += `<tr><td colspan="2" class="text-center text-muted fst-italic">… et ${items.length - 10} autres</td></tr>`;
            }

            previewSection.style.display = '';
        }

        function escapeHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        document.getElementById('separator').addEventListener('change', updatePreview);

        // Trigger preview if old content present (after validation error)
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('content').value.trim()) {
                updatePreview();
            }
        });
    </script>
</x-app-layout>
