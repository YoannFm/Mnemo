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

                {{-- Score --}}
                <div class="card p-3 mb-3" style="background:var(--accent-light);border:none;">
                    <div style="font-size:2rem;font-weight:800;color:var(--accent);">
                        {{ $attempt->grade }}
                    </div>
                    <div style="font-size:.9rem;color:var(--text-muted);">
                        {{ $attempt->score }} / {{ $attempt->total }} — {{ $attempt->percentage }}%
                    </div>
                </div>

                {{-- Classement --}}
                @if(\App\Models\Setting::get('feature_exam_ranking', '1'))
                @if ($rank && $totalParticipants > 1)
                    <div class="card p-3 mb-3">
                        <div style="font-size:.85rem;color:var(--text-muted);">
                            <i class="bi bi-trophy me-1" style="color:#fbbf24;"></i>
                            Vous êtes <strong>{{ $rank }}{{ $rank === 1 ? 'er' : 'ème' }}</strong>
                            sur {{ $totalParticipants }} participant{{ $totalParticipants > 1 ? 's' : '' }}
                        </div>
                    </div>
                @endif
                @endif

                <div class="card p-3 mb-3">
                    <p style="color:var(--text-muted);font-size:.85rem;margin:0;">
                        <i class="bi bi-info-circle me-1"></i>
                        Les résultats détaillés seront consultables après envoi par votre enseignant.
                    </p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-sm" style="border:1px solid var(--card-border);color:var(--text-muted);">
                    <i class="bi bi-house me-1"></i>Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
