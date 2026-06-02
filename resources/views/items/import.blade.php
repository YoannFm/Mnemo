<x-app-layout>
    <x-slot name="pageTitle">Import CSV - {{ $module->title }}</x-slot>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item"><a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a></li>
            <li class="breadcrumb-item"><a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a></li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Import CSV</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-spreadsheet" style="color:var(--accent);font-size:1.2rem;"></i>
                    <span class="fw-semibold">Import en masse via CSV</span>
                </div>
                <div class="card-body p-4">

                    {{-- Format attendu --}}
                    <div class="card mb-4" style="background:var(--accent-light);border:none;">
                        <div class="card-body p-3" style="font-size:.85rem;">
                            <strong>Format du fichier CSV :</strong>
                            <p style="color:var(--text-muted);margin:.5rem 0 0;">
                                Une ligne par item, 3 colonnes séparées par des virgules :<br>
                                <code style="color:var(--accent);">nom_français,nom_anglais,description/fonction</code>
                            </p>
                            <p style="color:var(--text-muted);margin:.5rem 0 0;font-size:.78rem;">
                                La première ligne peut contenir des en-têtes (elle sera ignorée automatiquement).<br>
                                Les photos ne sont pas importables via CSV — à ajouter manuellement après.
                            </p>
                        </div>
                    </div>

                    @if (session('import_errors'))
                        <div class="alert alert-danger mb-3" style="font-size:.82rem;">
                            @foreach (session('import_errors') as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('modules.items.import', $module) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Fichier CSV <span style="color:#ef4444;">*</span></label>
                            <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror"
                                   accept=".csv,.txt" required>
                            @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-upload me-1"></i> Importer
                            </button>
                            <a href="{{ route('modules.show', $module) }}" class="btn" style="color:var(--text-muted);border:1px solid var(--card-border);">Annuler</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
