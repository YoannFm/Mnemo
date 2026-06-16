<x-app-layout>
    <x-slot name="pageTitle">Examen partagé - {{ $sharedExam->module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-5">

            @if ($sharedExam->isExpired())
                <div class="card text-center py-5">
                    <i class="bi bi-clock-history" style="font-size:3rem;color:#ef4444;"></i>
                    <h5 class="mt-3">Lien expiré</h5>
                    <p style="color:var(--text-muted);font-size:.9rem;">
                        Ce lien d'examen a expiré. Contactez la personne qui vous l'a envoyé.
                    </p>
                </div>
            @else
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-pencil-square" style="font-size:2.5rem;color:var(--accent);"></i>
                        <h5 class="mt-3 mb-1">Examen partagé</h5>
                        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:.75rem;">
                            <strong>{{ $sharedExam->user->name }}</strong> vous a invité à passer un examen
                        </p>
                        <div class="card p-2 d-inline-block">
                            <span style="font-weight:600;">{{ $sharedExam->module->title }}</span>
                        </div>
                        @if ($sharedExam->label)
                            <div style="margin-top:.75rem;color:var(--text-muted);font-size:.85rem;">
                                <i class="bi bi-tag me-1"></i>{{ $sharedExam->label }}
                            </div>
                        @endif
                    </div>

                    @php
                        $attemptsDone = $sharedExam->attempts()->where('user_id', Auth::id())->count();
                        $attemptsLeft = $sharedExam->max_attempts - $attemptsDone;
                    @endphp

                    @if ($attemptsLeft > 0)
                    <p style="color:var(--text-muted);font-size:.85rem;text-align:center;">
                        Tentative {{ $attemptsDone + 1 }} / {{ $sharedExam->max_attempts }}
                    </p>
                    <form method="POST" action="{{ route('guest.exam.start', $sharedExam->uuid) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-fill me-1"></i>Commencer l'examen
                        </button>
                    </form>
                    @else
                    <div class="text-center" style="color:var(--text-muted);font-size:.875rem;">
                        <i class="bi bi-check-circle me-1" style="color:var(--success-color);"></i>
                        Vous avez utilisé toutes vos tentatives pour cet examen.
                    </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
