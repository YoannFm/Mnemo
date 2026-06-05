<x-app-layout>
    <x-slot name="pageTitle">{{ $module->title }}</x-slot>

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item">
                <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
            </li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">{{ $module->title }}</li>
        </ol>
    </nav>

    {{-- ── En-tête du module ── --}}
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0">{{ $module->title }}</h4>
                @if ($module->is_public)
                    <span class="badge-public"><i class="bi bi-globe2 me-1"></i>Public</span>
                @else
                    <span class="badge-private"><i class="bi bi-lock me-1"></i>Privé</span>
                @endif
            </div>
            @if ($module->description)
                <p style="color:var(--text-muted);font-size:.875rem;margin:0;">{{ $module->description }}</p>
            @endif
            <div class="mt-2" style="font-size:.78rem;color:var(--text-muted);">
                <i class="bi bi-card-list me-1"></i>
                {{ $items->total() }} item{{ $items->total() > 1 ? 's' : '' }}
                &nbsp;·&nbsp;
                <i class="bi bi-clock me-1"></i>
                Créé {{ $module->created_at->diffForHumans() }}
            </div>
        </div>

        {{-- Actions sur le module (uniquement pour le propriétaire) --}}
        @if (Auth::id() === $module->owner_id)
            <div class="d-flex gap-2 flex-wrap">
                {{-- Bouton d'ajout d'item --}}
                <a href="{{ route('modules.items.create', $module) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter un item
                </a>

                <a href="{{ route('modules.export', $module) }}"
                   class="btn"
                   style="color:var(--text-muted);border:1px solid var(--card-border);">
                    <i class="bi bi-file-earmark-zip me-1"></i> Exporter
                </a>
                <a href="{{ route('modules.edit', $module) }}"
                   class="btn"
                   style="color:var(--text-muted);border:1px solid var(--card-border);">
                    <i class="bi bi-pencil me-1"></i> Modifier
                </a>
                <form method="POST" action="{{ route('modules.destroy', $module) }}"
                      onsubmit="return confirm('Supprimer ce module et tous ses items ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn"
                            style="color:#ef4444;border:1px solid var(--card-border);">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        @elseif (Auth::check() && $module->is_public)
            {{-- Bouton Signaler (visible uniquement pour les utilisateurs connectés non propriétaires) --}}
            <div>
                <button type="button"
                        class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#reportModuleModal">
                    <i class="bi bi-flag me-1"></i> Signaler
                </button>
            </div>
        @endif
    </div>

    {{-- Modal Signalement --}}
    @auth
        @if ($module->is_public && Auth::id() !== $module->owner_id)
            <div class="modal fade" id="reportModuleModal" tabindex="-1" aria-labelledby="reportModuleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reportModuleModalLabel">
                                <i class="bi bi-flag me-2 text-danger"></i>Signaler ce module
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reportReason" class="form-label fw-semibold">Motif du signalement <span class="text-danger">*</span></label>
                                <select id="reportReason" class="form-select">
                                    <option value="">-- Choisir un motif --</option>
                                    <option value="spam">Spam</option>
                                    <option value="inappropriate">Contenu inapproprié</option>
                                    <option value="copyright">Violation de droits d'auteur</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="reportNote" class="form-label fw-semibold">Note (optionnelle)</label>
                                <textarea id="reportNote" class="form-control" rows="3" maxlength="500"
                                          placeholder="Précisez votre signalement..."></textarea>
                                <div class="form-text">Maximum 500 caractères.</div>
                            </div>
                            <div id="reportFeedback" class="d-none"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-danger" id="reportSubmitBtn">
                                <i class="bi bi-flag me-1"></i> Envoyer le signalement
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            document.getElementById('reportSubmitBtn')?.addEventListener('click', function () {
                const reason = document.getElementById('reportReason').value;
                const note   = document.getElementById('reportNote').value;
                const feedback = document.getElementById('reportFeedback');

                if (!reason) {
                    feedback.className = 'alert alert-warning';
                    feedback.textContent = 'Veuillez choisir un motif.';
                    return;
                }

                this.disabled = true;

                fetch('{{ route('modules.report', $module) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ reason, note })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'ok') {
                        feedback.className = 'alert alert-success';
                        feedback.textContent = 'Signalement envoyé. Merci !';
                        document.getElementById('reportSubmitBtn').classList.add('d-none');
                    } else if (data.status === 'already_reported') {
                        feedback.className = 'alert alert-info';
                        feedback.textContent = 'Vous avez déjà signalé ce module.';
                    } else {
                        feedback.className = 'alert alert-danger';
                        feedback.textContent = data.message ?? 'Une erreur est survenue.';
                        document.getElementById('reportSubmitBtn').disabled = false;
                    }
                    feedback.classList.remove('d-none');
                })
                .catch(() => {
                    feedback.className = 'alert alert-danger';
                    feedback.textContent = 'Erreur réseau. Veuillez réessayer.';
                    feedback.classList.remove('d-none');
                    document.getElementById('reportSubmitBtn').disabled = false;
                });
            });
            </script>
        @endif
    @endauth

    {{-- ── Boutons d'entraînement (Test / Anki) - nécessite au moins 4 items ── --}}
    @if ($items->total() >= 4)
        <div class="d-grid gap-2 mb-4" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <a href="{{ route('test.show', $module) }}" class="btn btn-primary" style="height:auto;padding:1rem;">
                <div style="font-size:1.5rem;margin-bottom:.25rem;">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div style="font-weight:600;font-size:.9rem;">Mode Test</div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.7);">Score final</div>
            </a>
            <a href="{{ route('anki.show', $module) }}" class="btn btn-outline-primary" style="height:auto;padding:1rem;border-width:2px;">
                <div style="font-size:1.5rem;margin-bottom:.25rem;">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div style="font-weight:600;font-size:.9rem;">Mode Anki</div>
                <div style="font-size:.75rem;color:var(--text-muted);">Infini</div>
            </a>
            <a href="{{ route('exam.show', $module) }}" class="btn btn-outline-secondary" style="height:auto;padding:1rem;border-width:2px;">
                <div style="font-size:1.5rem;margin-bottom:.25rem;">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div style="font-weight:600;font-size:.9rem;">Mode Examen</div>
                <div style="font-size:.75rem;color:var(--text-muted);">Tous les items</div>
            </a>
        </div>
    @if ($items->total() >= 4)
        @auth
            <div class="text-end mb-3" style="margin-top:-.5rem;">
                <form method="POST" action="{{ route('modules.progress.reset', $module) }}"
                      onsubmit="return confirm('Réinitialiser toute ta progression sur ce module ? Cette action est irréversible.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm"
                            style="color:var(--text-muted);border:1px solid var(--accent);background:transparent;">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser ma progression
                    </button>
                </form>
            </div>
        @endauth
    @endif

    @elseif ($items->total() > 0 && $items->total() < 4)
        <div class="alert d-flex align-items-center gap-2 mb-4"
             style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);color:#fbbf24;border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span style="font-size:.875rem;">
                Il faut au moins <strong>4 items</strong> pour s'entraîner.
                Encore {{ 4 - $items->total() }} à ajouter !
            </span>
        </div>
    @endif

    {{-- ── Notation du module ── --}}
    @auth
    <div class="card mb-4 p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <span class="fw-semibold" style="font-size:.95rem;">Notation du module</span>
                @if ($ratingCount > 0)
                    <span style="font-size:.8rem;color:var(--text-muted);margin-left:.5rem;">
                        {{ number_format($avgRating, 1) }}/5
                        <span style="color:var(--text-muted);">({{ $ratingCount }} avis)</span>
                    </span>
                @else
                    <span style="font-size:.8rem;color:var(--text-muted);margin-left:.5rem;">Aucun avis</span>
                @endif
            </div>
            {{-- Etoiles moyennes (lecture seule) --}}
            <div style="font-size:1.1rem;">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $ratingCount > 0 && $i <= round($avgRating) ? 'bi-star-fill' : 'bi-star' }}"
                       style="color:#fbbf24;"></i>
                @endfor
            </div>
        </div>

        {{-- Formulaire de notation --}}
        @if ($isMuted)
            <p style="font-size:.82rem;color:#ef4444;margin:0;">
                <i class="bi bi-mic-mute me-1"></i>Vous êtes muté et ne pouvez pas poster ou modifier un avis.
            </p>
        @elseif (Auth::id() !== $module->owner_id)
        <div id="rating-form-wrapper">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span style="font-size:.85rem;color:var(--text-muted);">
                    {{ $userRating ? 'Votre note :' : 'Notez ce module :' }}
                </span>
                <div class="d-flex gap-1" id="star-picker">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $userRating && $i <= $userRating->rating ? 'bi-star-fill' : 'bi-star' }} star-btn"
                           data-value="{{ $i }}"
                           style="font-size:1.4rem;cursor:pointer;color:#fbbf24;transition:transform .1s;">
                        </i>
                    @endfor
                </div>
            </div>
            <textarea id="rating-comment" class="form-control mb-2" rows="2"
                      maxlength="1000"
                      placeholder="Commentaire facultatif..."
                      style="font-size:.85rem;background:var(--card-bg);color:var(--text-primary);border-color:var(--card-border);">{{ $userRating?->comment }}</textarea>
            <button id="rating-submit" class="btn btn-sm btn-primary">
                {{ $userRating ? 'Modifier mon avis' : 'Envoyer mon avis' }}
            </button>
            <div id="rating-feedback" class="d-none mt-2" style="font-size:.82rem;"></div>
        </div>
        @elseif (Auth::id() === $module->owner_id)
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                <i class="bi bi-info-circle me-1"></i>Vous ne pouvez pas noter votre propre module.
            </p>
        @endif
    </div>

    <script>
    (function () {
        const stars = document.querySelectorAll('#star-picker .star-btn');
        const submitBtn = document.getElementById('rating-submit');
        const commentEl = document.getElementById('rating-comment');
        const feedback  = document.getElementById('rating-feedback');
        let selected = {{ $userRating ? $userRating->rating : 0 }};

        function renderStars(hovered) {
            const val = hovered || selected;
            stars.forEach((s, i) => {
                s.className = 'bi ' + (i < val ? 'bi-star-fill' : 'bi-star') + ' star-btn';
                s.style.fontSize = '1.4rem';
                s.style.cursor = 'pointer';
                s.style.color = '#fbbf24';
                s.style.transition = 'transform .1s';
            });
        }

        stars.forEach((s, i) => {
            s.addEventListener('mouseenter', () => renderStars(i + 1));
            s.addEventListener('mouseleave', () => renderStars(0));
            s.addEventListener('click', () => { selected = i + 1; renderStars(0); });
        });

        submitBtn?.addEventListener('click', function () {
            if (!selected) {
                feedback.className = 'alert alert-warning mt-2';
                feedback.textContent = 'Veuillez choisir une note.';
                feedback.classList.remove('d-none');
                return;
            }
            this.disabled = true;
            fetch('{{ route('modules.rate', $module) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rating: selected, comment: commentEl?.value ?? '' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'ok') {
                    feedback.className = 'alert alert-success mt-2';
                    feedback.textContent = 'Votre avis a bien été enregistré.';
                    submitBtn.textContent = 'Modifier mon avis';
                } else if (data.status === 'muted') {
                    feedback.className = 'alert alert-warning mt-2';
                    feedback.textContent = data.message ?? 'Vous êtes muté.';
                    submitBtn.disabled = true;
                } else {
                    feedback.className = 'alert alert-danger mt-2';
                    feedback.textContent = data.message ?? 'Une erreur est survenue.';
                    submitBtn.disabled = false;
                }
                feedback.classList.remove('d-none');
            })
            .catch(() => {
                feedback.className = 'alert alert-danger mt-2';
                feedback.textContent = 'Erreur réseau. Veuillez réessayer.';
                feedback.classList.remove('d-none');
                submitBtn.disabled = false;
            });
        });
    })();
    </script>
    @endauth

    {{-- ── Avis des utilisateurs ── --}}
    @php $allRatings = $module->ratings()->with('user')->whereNotNull('comment')->latest()->get(); @endphp
    @if ($allRatings->isNotEmpty())
        <div class="mb-4">
            <h6 class="mb-3" style="font-size:.9rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">
                Avis ({{ $allRatings->count() }})
            </h6>
            <div class="d-flex flex-column gap-2">
                @foreach ($allRatings as $r)
                    <div class="card p-3" style="gap:.4rem;display:flex;flex-direction:column;">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="fw-semibold" style="font-size:.85rem;">{{ $r->user?->name ?? 'Utilisateur supprimé' }}</span>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $r->rating ? 'bi-star-fill' : 'bi-star' }}" style="color:#fbbf24;font-size:.8rem;"></i>
                                    @endfor
                                </div>
                                @auth
                                    @if (Auth::id() !== $r->user_id)
                                        <button type="button"
                                                class="btn btn-sm report-rating-btn"
                                                style="color:var(--text-muted);border:none;background:transparent;padding:0;font-size:.8rem;"
                                                data-rating-id="{{ $r->id }}"
                                                title="Signaler cet avis">
                                            <i class="bi bi-flag"></i>
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                        <p style="font-size:.82rem;color:var(--text-muted);margin:0;">{{ $r->comment }}</p>
                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $r->created_at->diffForHumans() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Liste des items ── --}}
    @if ($items->isEmpty())
        {{-- État vide - aucun item dans le module --}}
        <div class="card text-center py-5">
            <i class="bi bi-image" style="font-size:3rem;color:var(--text-muted);"></i>
            <h6 class="mt-3">Aucun item dans ce module</h6>
            <p style="color:var(--text-muted);font-size:.875rem;">
                Ajoutez des éléments à mémoriser pour commencer à vous entraîner.
            </p>
            @if (Auth::id() === $module->owner_id)
                <a href="{{ route('modules.items.create', $module) }}"
                   class="btn btn-primary mx-auto" style="width:fit-content;">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter le premier item
                </a>
            @endif
        </div>
    @else
        <div class="row g-3">
            @foreach ($items as $item)
                {{-- Carte d'un item --}}
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card h-100">

                        {{-- Photo de l'item --}}
                        <div style="height:160px;overflow:hidden;border-radius:12px 12px 0 0;background:#0f1117;cursor:pointer;" onclick="openZoom('{{ $item->photo_url }}')">
                            <img src="{{ $item->photo_url }}"
                                 alt="{{ $item->name_fr }}"
                                 style="width:100%;height:100%;object-fit:cover;opacity:.9;">
                        </div>

                        <div class="card-body p-3" style="gap:.5rem;display:flex;flex-direction:column;">

                            {{-- Noms FR / EN --}}
                            <div>
                                <div class="fw-semibold" style="font-size:.95rem;">{{ $item->name_fr }}</div>
                                <div style="font-size:.8rem;color:var(--accent);"><span style="color:var(--text-muted);font-size:.75rem;">Traduction :</span> {{ $item->name_en }}</div>
                            </div>

                            {{-- Fonction/Description --}}
                            <p style="font-size:.78rem;color:var(--text-muted);margin:0;
                                      display:-webkit-box;-webkit-line-clamp:3;
                                      -webkit-box-orient:vertical;overflow:hidden;">
                                {{ $item->function_text }}
                            </p>

                            {{-- Boutons d'action (propriétaire uniquement) --}}
                            @if (Auth::id() === $module->owner_id)
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="{{ route('modules.items.edit', [$module, $item]) }}"
                                       class="btn btn-sm flex-grow-1"
                                       style="color:var(--text-muted);border:1px solid var(--card-border);">
                                        <i class="bi bi-pencil me-1"></i>Modifier
                                    </a>
                                    <form method="POST"
                                          action="{{ route('modules.items.destroy', [$module, $item]) }}"
                                          onsubmit="return confirm('Supprimer cet item ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm"
                                                style="color:#ef4444;border:1px solid var(--card-border);">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    @endif


{{-- Modal signalement d'avis --}}
@auth
<div class="modal fade" id="reportRatingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-flag me-2 text-danger"></i>Signaler cet avis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
                    <select id="reportRatingReason" class="form-select">
                        <option value="">-- Choisir un motif --</option>
                        <option value="spam">Spam</option>
                        <option value="inappropriate">Contenu inapproprié</option>
                        <option value="harassment">Harcèlement</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Note (optionnelle)</label>
                    <textarea id="reportRatingNote" class="form-control" rows="2" maxlength="500"
                              placeholder="Précisez..."></textarea>
                </div>
                <div id="reportRatingFeedback" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="reportRatingSubmit">
                    <i class="bi bi-flag me-1"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let currentRatingId = null;

    document.querySelectorAll('.report-rating-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            currentRatingId = this.dataset.ratingId;
            document.getElementById('reportRatingReason').value = '';
            document.getElementById('reportRatingNote').value = '';
            const fb = document.getElementById('reportRatingFeedback');
            fb.className = 'd-none';
            fb.textContent = '';
            document.getElementById('reportRatingSubmit').disabled = false;
            new bootstrap.Modal(document.getElementById('reportRatingModal')).show();
        });
    });

    document.getElementById('reportRatingSubmit')?.addEventListener('click', function () {
        const reason = document.getElementById('reportRatingReason').value;
        const note   = document.getElementById('reportRatingNote').value;
        const fb     = document.getElementById('reportRatingFeedback');

        if (!reason) {
            fb.className = 'alert alert-warning';
            fb.textContent = 'Veuillez choisir un motif.';
            fb.classList.remove('d-none');
            return;
        }

        this.disabled = true;

        fetch(`/module-ratings/${currentRatingId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            },
            body: JSON.stringify({ reason, note })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                fb.className = 'alert alert-success';
                fb.textContent = 'Signalement envoyé. Merci !';
                document.getElementById('reportRatingSubmit').classList.add('d-none');
            } else if (data.status === 'already_reported') {
                fb.className = 'alert alert-info';
                fb.textContent = 'Vous avez déjà signalé cet avis.';
            } else {
                fb.className = 'alert alert-danger';
                fb.textContent = data.message ?? 'Une erreur est survenue.';
                document.getElementById('reportRatingSubmit').disabled = false;
            }
            fb.classList.remove('d-none');
        })
        .catch(() => {
            fb.className = 'alert alert-danger';
            fb.textContent = 'Erreur réseau. Veuillez réessayer.';
            fb.classList.remove('d-none');
            document.getElementById('reportRatingSubmit').disabled = false;
        });
    });
})();
</script>
@endauth

<div id="zoom-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;cursor:pointer;align-items:center;justify-content:center;" onclick="this.style.display='none'">
    <img id="zoom-img" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;">
</div>

<script>
function openZoom(src) {
    document.getElementById('zoom-img').src = src;
    document.getElementById('zoom-modal').style.display = 'flex';
}
</script>

</x-app-layout>
