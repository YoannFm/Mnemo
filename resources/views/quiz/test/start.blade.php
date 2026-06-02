<x-app-layout>
    <x-slot name="pageTitle">Mode Test - {{ $module->title }}</x-slot>

    {{-- Fil d'Ariane --}}
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
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge" style="color:var(--accent);font-size:1.2rem;"></i>
                    <span class="fw-semibold">Mode Test</span>
                </div>
                <div class="card-body p-4">

                    <h5 class="mb-3">{{ $module->title }}</h5>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:2rem;">
                        Répondez à un nombre fixe de questions pour obtenir un score final.
                        Chaque réponse sera enregistrée dans votre historique.
                    </p>

                    {{-- Formulaire de choix du nombre de questions --}}
                    <form method="POST" action="{{ route('test.start', $module) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="question_count" class="form-label">
                                Nombre de questions <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="number"
                                   id="question_count"
                                   name="question_count"
                                   class="form-control @error('question_count') is-invalid @enderror"
                                   value="{{ old('question_count', 10) }}"
                                   min="1"
                                   max="{{ $module->items()->count() }}"
                                   required>
                            @error('question_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem;">
                                Items disponibles : {{ $module->items()->count() }} -
                                Vous pouvez choisir entre 1 et {{ $module->items()->count() }} questions.
                            </div>
                        </div>

                        {{-- Infos supplémentaires --}}
                        <div class="card" style="background:var(--accent-light);border:none;margin-bottom:1.5rem;">
                            <div class="card-body p-3" style="font-size:.85rem;">
                                <div class="d-flex gap-2 mb-2">
                                    <i class="bi bi-info-circle" style="color:var(--accent);"></i>
                                    <div>
                                        <strong>Comment ça marche ?</strong>
                                        <p style="margin:0;color:var(--text-muted);">
                                            Chaque question montre une photo, une fonction ou un nom, et vous devez choisir la bonne réponse parmi 3 propositions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Boutons d'action --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-play-fill me-1"></i> Commencer le test
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

</x-app-layout>
