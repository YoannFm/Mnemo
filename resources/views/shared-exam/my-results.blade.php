<x-app-layout>
    <x-slot name="pageTitle">Mes résultats d'examens</x-slot>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1" style="font-weight:800;letter-spacing:-.5px;">Mes résultats d'examens</h4>
            <p style="color:var(--text-muted);font-size:.875rem;margin:0;">
                Historique de vos participations aux examens partagés.
            </p>
        </div>
    </div>

    @if ($attempts->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-clipboard-check" style="font-size:3rem;color:var(--text-muted);"></i>
            <p class="mt-3" style="color:var(--text-muted);">Vous n'avez pas encore passé d'examen partagé.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach ($attempts as $attempt)
                @php $pct = $attempt->percentage; @endphp
                <div class="card">
                    <div class="card-body p-0">

                        {{-- Header --}}
                        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
                            <div>
                                <div style="font-weight:600;font-size:.95rem;">
                                    {{ $attempt->sharedExam->label ?? 'Examen partagé' }}
                                </div>
                                <div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem;">
                                    <i class="bi bi-collection me-1"></i>{{ $attempt->sharedExam->module->title }}
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-person me-1"></i>Créé par {{ $attempt->sharedExam->user->name }}
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-calendar me-1"></i>{{ $attempt->finished_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div style="text-align:center;">
                                    <div style="font-size:1.5rem;font-weight:800;line-height:1;
                                        color:{{ $pct >= 70 ? '#22c55e' : ($pct >= 50 ? '#fbbf24' : '#ef4444') }}">
                                        {{ $pct }}%
                                    </div>
                                    <div style="font-size:.72rem;color:var(--text-muted);">{{ $attempt->score }}/{{ $attempt->total }}</div>
                                </div>
                                <button class="btn btn-sm"
                                        style="color:var(--text-muted);border:1px solid var(--card-border);font-size:.8rem;"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $attempt->id }}">
                                    <i class="bi bi-chevron-down me-1"></i>Détail
                                </button>
                            </div>
                        </div>

                        {{-- Détail --}}
                        <div class="collapse" id="detail-{{ $attempt->id }}">
                            <div style="padding:.75rem 1.25rem;">
                                @if ($attempt->answers && count($attempt->answers) > 0)
                                    @foreach ($attempt->answers as $ans)
                                        <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.5rem 0;border-bottom:1px solid var(--card-border);">
                                            <span style="font-size:1rem;margin-top:.1rem;flex-shrink:0;">
                                                @if ($ans['is_correct'])
                                                    <i class="bi bi-check-circle-fill" style="color:#22c55e;"></i>
                                                @else
                                                    <i class="bi bi-x-circle-fill" style="color:#ef4444;"></i>
                                                @endif
                                            </span>
                                            <div style="font-size:.82rem;flex:1;">
                                                <div style="color:var(--text-muted);margin-bottom:.2rem;">{{ $ans['question_text'] ?? '-' }}</div>
                                                <div>
                                                    Votre réponse :
                                                    <strong style="color:{{ $ans['is_correct'] ? '#22c55e' : '#ef4444' }}">
                                                        {{ $ans['user_answer'] ?? '-' }}
                                                    </strong>
                                                </div>
                                                @if (!$ans['is_correct'])
                                                    <div style="color:var(--text-muted);">
                                                        Bonne réponse : <strong style="color:#22c55e;">{{ $ans['correct_answer'] ?? '-' }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Aucun détail disponible.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-app-layout>
