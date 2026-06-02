<x-app-layout>
    <x-slot name="pageTitle">Résultat — {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Carte de résultat --}}
            <div class="card text-center mb-4">
                <div class="card-body p-5">

                    {{-- Score en grand --}}
                    <div style="margin-bottom:1.5rem;">
                        <div style="font-size:5rem;font-weight:700;color:var(--accent);">
                            {{ $percentage }}%
                        </div>
                    </div>

                    {{-- Score numérique --}}
                    <h5 class="mb-2">{{ $score }} sur {{ $total }} bonnes réponses</h5>
                    <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:2rem;">
                        Test complété le {{ now()->format('d/m/Y à H:i') }}
                    </p>

                    {{-- Évaluation visuelle --}}
                    <div class="mb-4">
                        @if ($percentage >= 80)
                            <span style="font-size:2rem;">🎉</span>
                            <p style="color:var(--success-color);font-weight:600;">Excellent !</p>
                        @elseif ($percentage >= 60)
                            <span style="font-size:2rem;">👍</span>
                            <p style="color:var(--accent);font-weight:600;">Bien !</p>
                        @elseif ($percentage >= 40)
                            <span style="font-size:2rem;">💪</span>
                            <p style="color:#fbbf24;font-weight:600;">Continuez !</p>
                        @else
                            <span style="font-size:2rem;">📚</span>
                            <p style="color:#ef4444;font-weight:600;">À revoir…</p>
                        @endif
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                        <a href="{{ route('test.show', $module) }}" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Relancer un test
                        </a>
                        <a href="{{ route('modules.show', $module) }}" class="btn" style="color:var(--accent);border:2px solid var(--accent);">
                            <i class="bi bi-arrow-left me-1"></i> Retour au module
                        </a>
                    </div>

                </div>
            </div>

            {{-- Détail des réponses --}}
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold">Détail des réponses</span>
                </div>
                <div class="card-body">

                    @forelse ($answers as $index => $answer)
                        <div class="mb-3 pb-3" style="border-bottom:1px solid var(--card-border);">

                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <div style="font-size:.8rem;color:var(--text-muted);">
                                        Question {{ $index + 1 }} — {{ $answer['question_type'] }}
                                    </div>
                                </div>
                                @if ($answer['is_correct'])
                                    <span style="color:var(--success-color);font-weight:600;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Correct
                                    </span>
                                @else
                                    <span style="color:#ef4444;font-weight:600;">
                                        <i class="bi bi-x-circle-fill me-1"></i>Incorrect
                                    </span>
                                @endif
                            </div>

                            <div style="font-size:.85rem;color:var(--text-muted);">
                                <div><strong>Votre réponse :</strong> {{ $answer['user_answer'] }}</div>
                                @if (!$answer['is_correct'])
                                    <div><strong>Bonne réponse :</strong> {{ $answer['correct_answer'] }}</div>
                                @endif
                            </div>

                        </div>
                    @empty
                        <p style="color:var(--text-muted);text-align:center;">Aucune réponse.</p>
                    @endforelse

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
