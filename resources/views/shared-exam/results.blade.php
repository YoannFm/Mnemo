<x-app-layout>
    <x-slot name="pageTitle">Résultats — {{ $sharedExam->label ?? 'Examen partagé' }}</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('modules.show', $sharedExam->module) }}" style="color:var(--accent);">{{ $sharedExam->module->title }}</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">Résultats partagés</li>
        </ol>
    </nav>

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ $sharedExam->label ?? 'Examen partagé' }}</h4>
            <div style="font-size:.85rem;color:var(--text-muted);">
                <i class="bi bi-journals me-1"></i>{{ $sharedExam->module->title }}
                &nbsp;·&nbsp;
                <i class="bi bi-calendar me-1"></i>Créé {{ $sharedExam->created_at->diffForHumans() }}
                @if ($sharedExam->isExpired())
                    &nbsp;·&nbsp;<span style="color:#ef4444;"><i class="bi bi-clock-history me-1"></i>Expiré</span>
                @elseif ($sharedExam->expires_at)
                    &nbsp;·&nbsp;<span style="color:#fbbf24;"><i class="bi bi-clock me-1"></i>Expire {{ $sharedExam->expires_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('modules.show', $sharedExam->module) }}" class="btn btn-sm"
           style="color:var(--text-muted);border:1px solid var(--card-border);">
            <i class="bi bi-arrow-left me-1"></i>Retour au module
        </a>
    </div>

    {{-- Lien partageable --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem;">
                <i class="bi bi-link-45deg me-1"></i>Lien partageable
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <code style="font-size:.85rem;color:var(--accent);word-break:break-all;">{{ route('guest.exam.show', $sharedExam->uuid) }}</code>
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ route('guest.exam.show', $sharedExam->uuid) }}').then(()=>this.innerHTML='<i class=\'bi bi-check-lg\'></i> Copié')"
                        class="btn btn-sm"
                        style="color:var(--text-muted);border:1px solid var(--card-border);white-space:nowrap;">
                    <i class="bi bi-clipboard"></i> Copier
                </button>
            </div>
        </div>
    </div>

    {{-- Résultats --}}
    <div class="card">
        <div class="card-body p-0">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--card-border);">
                <h6 class="mb-0">
                    <i class="bi bi-people me-1"></i>
                    {{ $attempts->count() }} participant{{ $attempts->count() > 1 ? 's' : '' }}
                </h6>
            </div>

            @if ($attempts->isEmpty())
                <div class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                    <p class="mt-2 mb-0" style="font-size:.9rem;">Aucun résultat pour l'instant.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.875rem;">
                        <thead>
                            <tr style="color:var(--text-muted);">
                                <th style="padding:.75rem 1.25rem;">Participant</th>
                                <th>Score</th>
                                <th>%</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attempts as $attempt)
                                <tr>
                                    <td style="padding:.75rem 1.25rem;font-weight:500;">{{ $attempt->guest_name }}</td>
                                    <td>{{ $attempt->score }} / {{ $attempt->total }}</td>
                                    <td>
                                        @php $pct = $attempt->percentage; @endphp
                                        @if ($pct >= 70)
                                            <span class="badge" style="background:rgba(34,197,94,.15);color:#22c55e;">{{ $pct }}%</span>
                                        @elseif ($pct >= 50)
                                            <span class="badge" style="background:rgba(251,191,36,.15);color:#fbbf24;">{{ $pct }}%</span>
                                        @else
                                            <span class="badge" style="background:rgba(239,68,68,.15);color:#ef4444;">{{ $pct }}%</span>
                                        @endif
                                    </td>
                                    <td style="color:var(--text-muted);">
                                        {{ $attempt->finished_at ? $attempt->finished_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm"
                                                style="color:var(--text-muted);border:1px solid var(--card-border);font-size:.8rem;"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#detail-{{ $attempt->id }}"
                                                aria-expanded="false">
                                            <i class="bi bi-chevron-down"></i> Détail
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="detail-{{ $attempt->id }}">
                                    <td colspan="5" style="padding:0 1.25rem 1rem;background:var(--card-bg);">
                                        <div style="padding-top:.75rem;">
                                            @if ($attempt->answers && count($attempt->answers) > 0)
                                                @foreach ($attempt->answers as $i => $ans)
                                                    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.5rem 0;border-bottom:1px solid var(--card-border);">
                                                        <span style="font-size:1rem;margin-top:.1rem;">
                                                            @if ($ans['is_correct'])
                                                                <i class="bi bi-check-circle-fill" style="color:#22c55e;"></i>
                                                            @else
                                                                <i class="bi bi-x-circle-fill" style="color:#ef4444;"></i>
                                                            @endif
                                                        </span>
                                                        <div style="font-size:.82rem;flex:1;">
                                                            <div style="color:var(--text-muted);margin-bottom:.2rem;">{{ $ans['question_text'] ?? '—' }}</div>
                                                            <div>
                                                                Réponse donnée :
                                                                <strong style="color:{{ $ans['is_correct'] ? '#22c55e' : '#ef4444' }}">
                                                                    {{ $ans['user_answer'] ?? '—' }}
                                                                </strong>
                                                            </div>
                                                            @if (!$ans['is_correct'])
                                                                <div style="color:var(--text-muted);">
                                                                    Bonne réponse : <strong style="color:#22c55e;">{{ $ans['correct_answer'] ?? '—' }}</strong>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Aucun détail disponible.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</x-app-layout>
