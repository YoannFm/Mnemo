<x-app-layout>
    <x-slot name="pageTitle">Mes examens partagés</x-slot>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1" style="font-weight:800;letter-spacing:-.5px;">Mes examens partagés</h4>
            <p style="color:var(--text-muted);font-size:.875rem;margin:0;">
                Liens d'examen que vous avez créés et résultats des participants.
            </p>
        </div>
    </div>

    @if ($sharedExams->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-share" style="font-size:3rem;color:var(--text-muted);"></i>
            <p class="mt-3" style="color:var(--text-muted);">Vous n'avez pas encore créé de lien d'examen partagé.</p>
            <p style="color:var(--text-muted);font-size:.85rem;">
                Allez sur un module → <strong>Mode Examen</strong> pour en générer un.
            </p>
            <a href="{{ route('modules.index') }}" class="btn btn-primary mx-auto" style="width:fit-content;">
                <i class="bi bi-collection me-1"></i> Mes modules
            </a>
        </div>
    @else
        <div class="row g-3">
            @foreach ($sharedExams as $se)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column p-3" style="gap:.6rem;">
                            {{-- Titre + statut --}}
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <h6 class="mb-0 fw-semibold">{{ $se->label ?? 'Sans étiquette' }}</h6>
                                @if ($se->isExpired())
                                    <span style="background:rgba(239,68,68,.12);color:#ef4444;font-size:.72rem;padding:.15rem .5rem;border-radius:.25rem;white-space:nowrap;">Expiré</span>
                                @else
                                    <span style="background:rgba(34,197,94,.12);color:#22c55e;font-size:.72rem;padding:.15rem .5rem;border-radius:.25rem;white-space:nowrap;">Actif</span>
                                @endif
                            </div>

                            {{-- Module --}}
                            <div style="font-size:.8rem;color:var(--text-muted);">
                                <i class="bi bi-collection me-1"></i>
                                <a href="{{ route('modules.show', $se->module) }}" style="color:var(--text-muted);">{{ $se->module->title }}</a>
                            </div>

                            {{-- Stats --}}
                            <div style="font-size:.8rem;color:var(--text-muted);">
                                <i class="bi bi-people me-1"></i>{{ $se->attempts_count }} participant{{ $se->attempts_count > 1 ? 's' : '' }}
                                @if ($se->expires_at && !$se->isExpired())
                                    &nbsp;·&nbsp;<i class="bi bi-clock me-1"></i>Expire {{ $se->expires_at->diffForHumans() }}
                                @endif
                            </div>

                            {{-- Lien --}}
                            <div style="background:var(--card-border);border-radius:6px;padding:.4rem .6rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                                <code style="font-size:.72rem;color:var(--accent);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">{{ route('guest.exam.show', $se->uuid) }}</code>
                                <button type="button"
                                        onclick="copyExamLink('{{ route('guest.exam.show', $se->uuid) }}', this)"
                                        style="background:none;border:none;color:var(--text-muted);padding:0;cursor:pointer;flex-shrink:0;">
                                    <i class="bi bi-clipboard" style="font-size:.9rem;"></i>
                                </button>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('shared-exam.results', $se) }}" class="btn btn-sm btn-primary flex-fill">
                                    <i class="bi bi-bar-chart me-1"></i>Résultats
                                </a>
                                <form method="POST" action="{{ route('shared-exam.destroy', $se) }}"
                                      onsubmit="return confirm('Supprimer ce lien ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                            style="color:#ef4444;border:1px solid var(--card-border);">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script>
    function copyExamLink(url, btn) {
        navigator.clipboard.writeText(url).then(function() {
            var icon = btn.querySelector('i');
            icon.className = 'bi bi-check2';
            icon.style.color = '#22c55e';
            setTimeout(function() {
                icon.className = 'bi bi-clipboard';
                icon.style.color = '';
            }, 2000);
        });
    }
    </script>
</x-app-layout>
