<x-app-layout>
    <x-slot name="pageTitle">Examen terminé</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-5">
            <div class="card p-4 text-center">
                <i class="bi bi-check-circle-fill" style="font-size:3.5rem;color:var(--success-color);"></i>
                <h4 class="mt-3 mb-2">Examen terminé !</h4>
                <p style="color:var(--text-muted);font-size:.95rem;margin-bottom:1.5rem;">
                    Vos résultats ont été transmis à
                    <strong>{{ $sharedExam->user->name }}</strong>.
                </p>
                <div class="card p-3 mb-3">
                    <p style="color:var(--text-muted);font-size:.85rem;margin:0;">
                        <i class="bi bi-info-circle me-1"></i>
                        Merci d'avoir participé. Les résultats détaillés seront consultés par votre enseignant.
                    </p>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('my-exam-results') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-clipboard-check me-1"></i>Voir mes résultats
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm" style="border:1px solid var(--card-border);color:var(--text-muted);">
                        <i class="bi bi-house me-1"></i>Accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
