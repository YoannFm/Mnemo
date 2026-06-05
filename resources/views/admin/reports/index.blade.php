<x-admin-layout>
    <x-slot name="pageTitle">Signalements</x-slot>

    {{-- Filtre --}}
    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('admin.reports.index', ['type' => 'comments']) }}"
           class="btn btn-sm {{ $type === 'comments' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-chat-left-text me-1"></i> Commentaires
            <span class="badge {{ $type === 'comments' ? 'bg-light text-dark' : 'bg-secondary' }} ms-1">{{ $commentReports->total() }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['type' => 'modules']) }}"
           class="btn btn-sm {{ $type === 'modules' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-collection me-1"></i> Modules
            <span class="badge {{ $type === 'modules' ? 'bg-light text-dark' : 'bg-secondary' }} ms-1">{{ $moduleReports->total() }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['type' => 'ratings']) }}"
           class="btn btn-sm {{ $type === 'ratings' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-star me-1"></i> Avis
            <span class="badge {{ $type === 'ratings' ? 'bg-light text-dark' : 'bg-secondary' }} ms-1">{{ $ratingReports->total() }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['type' => 'replies']) }}"
           class="btn btn-sm {{ $type === 'replies' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-reply me-1"></i> Réponses
            <span class="badge {{ $type === 'replies' ? 'bg-light text-dark' : 'bg-secondary' }} ms-1">{{ $replyReports->total() }}</span>
        </a>
    </div>

    @if($type === 'comments')
    {{-- Signalements de commentaires --}}
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Signalements de commentaires</h5></div>
        <div class="card-body">
            @if($commentReports->isEmpty())
                <p class="text-muted mb-0">Aucun signalement de commentaire.</p>
            @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Signalé par</th>
                            <th>Commentaire de</th>
                            <th>Article</th>
                            <th>Commentaire signalé</th>
                            <th>Motif</th>
                            <th>Note</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commentReports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    @if($report->user)
                                        <a href="{{ route('admin.users.edit', $report->user) }}">{{ $report->user->name }}</a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @if($report->postComment && $report->postComment->user)
                                        <a href="{{ route('admin.users.edit', $report->postComment->user) }}">{{ $report->postComment->user->name }}</a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @if($report->postComment && $report->postComment->post)
                                        <a href="{{ route('posts.show', $report->postComment->post) }}" target="_blank">
                                            {{ Str::limit($report->postComment->post->title, 30) }}
                                        </a>
                                    @else -
                                    @endif
                                </td>
                                <td style="max-width:250px;">
                                    @if($report->postComment)
                                        @if($report->postComment->is_deleted)
                                            <em class="text-muted">[Commentaire supprimé]</em>
                                        @else
                                            <span class="d-inline-block text-truncate" style="max-width:220px;"
                                                  data-bs-toggle="tooltip" data-bs-placement="top"
                                                  title="{{ e($report->postComment->content) }}">
                                                {{ $report->postComment->content }}
                                            </span>
                                        @endif
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @php $motifs = ['spam'=>'Spam','harassment'=>'Harcèlement','inappropriate'=>'Inapproprié','misinformation'=>'Désinformation','other'=>'Autre']; @endphp
                                    <span class="badge bg-warning text-dark">{{ $motifs[$report->reason] ?? $report->reason }}</span>
                                </td>
                                <td style="font-size:.8rem;max-width:200px;">{{ $report->note ?: '-' }}</td>
                                <td>
                                    @if($report->status === 'pending')
                                        <span class="badge bg-danger">En attente</span>
                                    @elseif($report->status === 'sanctioned')
                                        <span class="badge bg-success">Sanctionné</span>
                                    @else
                                        <span class="badge bg-secondary">Non sanctionné</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($report->status === 'pending')
                                            <form method="POST" action="{{ route('admin.reports.sanctioned', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Sanctionné">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reports.unsanctioned', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Non sanctionné">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($report->user)
                                            <a href="{{ route('admin.notifications.create') }}?user_id={{ $report->user->id }}"
                                               class="btn btn-sm btn-outline-info" title="Notifier le signaleur">
                                                <i class="bi bi-bell"></i>
                                            </a>
                                        @endif
                                        @if($report->postComment && $report->postComment->user)
                                            <a href="{{ route('admin.users.edit', $report->postComment->user) }}"
                                               class="btn btn-sm btn-outline-danger" title="Gérer le signalé">
                                                <i class="bi bi-person-x"></i>
                                            </a>
                                        @endif
                                        @if($report->postComment && !$report->postComment->is_deleted)
                                            <form method="POST" action="{{ route('admin.reports.delete-comment', $report) }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer ce commentaire ?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer le commentaire">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($report->postComment && $report->postComment->user)
                                            <button type="button" class="btn btn-sm btn-warning" title="Muter l'utilisateur"
                                                    data-bs-toggle="modal" data-bs-target="#muteModal{{ $report->id }}">
                                                <i class="bi bi-mic-mute"></i>
                                            </button>
                                            <div class="modal fade" id="muteModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.reports.mute-user', $report) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Muter {{ $report->postComment->user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Durée</label>
                                                                    <div class="input-group">
                                                                        <input type="number" name="duration_value" class="form-control" min="1" placeholder="ex: 27" style="max-width:100px;">
                                                                        <select name="duration_unit" class="form-select">
                                                                            <option value="minutes">Minutes</option>
                                                                            <option value="hours">Heures</option>
                                                                            <option value="days" selected>Jours</option>
                                                                            <option value="weeks">Semaines</option>
                                                                            <option value="months">Mois</option>
                                                                        </select>
                                                                        <div class="input-group-text">
                                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="duration_permanent" id="perm{{ $report->id }}" value="1"
                                                                                   onchange="toggleDuration(this)">
                                                                            <label class="form-check-label" for="perm{{ $report->id }}">Définitif</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Raison</label>
                                                                    <textarea name="reason" class="form-control" rows="3" maxlength="500" required placeholder="Motif du mute..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-warning">Confirmer le mute</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $commentReports->links() }}
            @endif
        </div>
    </div>
    @endif

    @if($type === 'modules')
    {{-- Signalements de modules --}}
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Signalements de modules</h5></div>
        <div class="card-body">
            @if($moduleReports->isEmpty())
                <p class="text-muted mb-0">Aucun signalement de module.</p>
            @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Signalé par</th>
                            <th>Module signalé</th>
                            <th>Auteur du module</th>
                            <th>Motif</th>
                            <th>Note</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moduleReports as $report)
                            @php $motifs = ['spam'=>'Spam','inappropriate'=>'Inapproprié','copyright'=>'Droits d\'auteur','other'=>'Autre']; @endphp
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    @if($report->user)
                                        <a href="{{ route('admin.users.edit', $report->user) }}">{{ $report->user->name }}</a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @if($report->module)
                                        <a href="{{ route('modules.show', $report->module) }}" target="_blank">
                                            {{ Str::limit($report->module->title, 35) }}
                                        </a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @if($report->module && $report->module->owner)
                                        <a href="{{ route('admin.users.edit', $report->module->owner) }}">{{ $report->module->owner->name }}</a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $motifs[$report->reason] ?? $report->reason }}</span>
                                </td>
                                <td style="font-size:.8rem;max-width:200px;">{{ $report->note ?: '-' }}</td>
                                <td>
                                    @if($report->status === 'pending')
                                        <span class="badge bg-danger">En attente</span>
                                    @elseif($report->status === 'treated')
                                        <span class="badge bg-success">Traité</span>
                                    @else
                                        <span class="badge bg-secondary">Rejeté</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($report->status === 'pending')
                                            <form method="POST" action="{{ route('admin.reports.modules.treated', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer traité">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reports.modules.rejected', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Rejeter">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($report->user)
                                            <a href="{{ route('admin.notifications.create') }}?user_id={{ $report->user->id }}"
                                               class="btn btn-sm btn-outline-info" title="Notifier le signaleur">
                                                <i class="bi bi-bell"></i>
                                            </a>
                                        @endif
                                        @if($report->module && $report->module->owner)
                                            <a href="{{ route('admin.users.edit', $report->module->owner) }}"
                                               class="btn btn-sm btn-outline-danger" title="Gérer l'auteur">
                                                <i class="bi bi-person-x"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $moduleReports->links() }}
            @endif
        </div>
    </div>
    @endif

    @if($type === 'ratings')
    {{-- Signalements d'avis --}}
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Signalements d'avis sur les modules</h5></div>
        <div class="card-body">
            @if($ratingReports->isEmpty())
                <p class="text-muted mb-0">Aucun signalement d'avis.</p>
            @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Signalé par</th>
                            <th>Auteur de l'avis</th>
                            <th>Module</th>
                            <th>Avis</th>
                            <th>Note</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ratingReports as $report)
                            @php $motifs = ['spam'=>'Spam','inappropriate'=>'Inapproprié','harassment'=>'Harcèlement','other'=>'Autre']; @endphp
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    @if($report->user)
                                        <a href="{{ route('admin.users.edit', $report->user) }}">{{ $report->user->name }}</a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @if($report->moduleRating && $report->moduleRating->user)
                                        <a href="{{ route('admin.users.edit', $report->moduleRating->user) }}">{{ $report->moduleRating->user->name }}</a>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    @if($report->moduleRating && $report->moduleRating->module)
                                        <a href="{{ route('modules.show', $report->moduleRating->module) }}" target="_blank">
                                            {{ Str::limit($report->moduleRating->module->title, 30) }}
                                        </a>
                                    @else -
                                    @endif
                                </td>
                                <td style="max-width:200px;font-size:.8rem;">
                                    @if($report->moduleRating)
                                        @for($i=1;$i<=5;$i++)
                                            <i class="bi {{ $i <= $report->moduleRating->rating ? 'bi-star-fill' : 'bi-star' }}" style="color:#fbbf24;font-size:.8rem;"></i>
                                        @endfor
                                        @if($report->moduleRating->comment)
                                            <br><span class="text-muted">{{ Str::limit($report->moduleRating->comment, 80) }}</span>
                                        @endif
                                    @else
                                        <em class="text-muted">[Avis supprimé]</em>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;max-width:150px;">{{ $report->note ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $motifs[$report->reason] ?? $report->reason }}</span>
                                </td>
                                <td>
                                    @if($report->status === 'pending')
                                        <span class="badge bg-danger">En attente</span>
                                    @elseif($report->status === 'treated')
                                        <span class="badge bg-success">Traité</span>
                                    @else
                                        <span class="badge bg-secondary">Rejeté</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($report->status === 'pending')
                                            <form method="POST" action="{{ route('admin.reports.ratings.treated', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer traité">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reports.ratings.rejected', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Rejeter">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($report->moduleRating)
                                            <form method="POST" action="{{ route('admin.reports.ratings.delete', $report) }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cet avis ?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer l'avis">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($report->moduleRating && $report->moduleRating->user)
                                            <button type="button" class="btn btn-sm btn-warning" title="Muter l'utilisateur"
                                                    data-bs-toggle="modal" data-bs-target="#muteRatingModal{{ $report->id }}">
                                                <i class="bi bi-mic-mute"></i>
                                            </button>
                                            <div class="modal fade" id="muteRatingModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.reports.ratings.mute-user', $report) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Muter {{ $report->moduleRating->user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Durée</label>
                                                                    <div class="input-group">
                                                                        <input type="number" name="duration_value" class="form-control" min="1" placeholder="ex: 7" style="max-width:100px;">
                                                                        <select name="duration_unit" class="form-select">
                                                                            <option value="minutes">Minutes</option>
                                                                            <option value="hours">Heures</option>
                                                                            <option value="days" selected>Jours</option>
                                                                            <option value="weeks">Semaines</option>
                                                                            <option value="months">Mois</option>
                                                                        </select>
                                                                        <div class="input-group-text">
                                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="duration_permanent" id="permR{{ $report->id }}" value="1"
                                                                                   onchange="toggleDuration(this)">
                                                                            <label class="form-check-label" for="permR{{ $report->id }}">Définitif</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Raison</label>
                                                                    <textarea name="reason" class="form-control" rows="3" maxlength="500" required placeholder="Motif du mute..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-warning">Confirmer le mute</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $ratingReports->links() }}
            @endif
        </div>
    </div>
    @endif

    @if($type === 'replies')
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Signalements de réponses</h5></div>
        <div class="card-body">
            @if($replyReports->isEmpty())
                <p class="text-muted mb-0">Aucun signalement de réponse.</p>
            @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Signalé par</th>
                            <th>Auteur de la réponse</th>
                            <th>Module</th>
                            <th>Réponse</th>
                            <th>Note</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($replyReports as $report)
                            @php $motifs = ['spam'=>'Spam','inappropriate'=>'Inapproprié','harassment'=>'Harcèlement','other'=>'Autre']; @endphp
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    @if($report->user)
                                        <a href="{{ route('admin.users.edit', $report->user) }}">{{ $report->user->name }}</a>
                                    @else - @endif
                                </td>
                                <td>
                                    @if($report->reply && $report->reply->user)
                                        <a href="{{ route('admin.users.edit', $report->reply->user) }}">{{ $report->reply->user->name }}</a>
                                    @else - @endif
                                </td>
                                <td>
                                    @if($report->reply && $report->reply->moduleRating && $report->reply->moduleRating->module)
                                        <a href="{{ route('modules.show', $report->reply->moduleRating->module) }}" target="_blank">
                                            {{ Str::limit($report->reply->moduleRating->module->title, 25) }}
                                        </a>
                                    @else - @endif
                                </td>
                                <td style="max-width:200px;font-size:.8rem;">
                                    @if($report->reply)
                                        {{ Str::limit($report->reply->content, 80) }}
                                    @else
                                        <em class="text-muted">[Supprimée]</em>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;max-width:150px;">{{ $report->note ?: '-' }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $motifs[$report->reason] ?? $report->reason }}</span></td>
                                <td>
                                    @if($report->status === 'pending') <span class="badge bg-danger">En attente</span>
                                    @elseif($report->status === 'treated') <span class="badge bg-success">Traité</span>
                                    @else <span class="badge bg-secondary">Rejeté</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($report->status === 'pending')
                                            <form method="POST" action="{{ route('admin.reports.replies.treated', $report) }}" class="d-inline">
                                                @csrf <button type="submit" class="btn btn-sm btn-outline-success" title="Traité"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.reports.replies.rejected', $report) }}" class="d-inline">
                                                @csrf <button type="submit" class="btn btn-sm btn-outline-secondary" title="Rejeter"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        @endif
                                        @if($report->reply)
                                            <form method="POST" action="{{ route('admin.reports.replies.delete', $report) }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cette réponse ?')">
                                                @csrf <button type="submit" class="btn btn-sm btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                        @if($report->reply && $report->reply->user)
                                            <button type="button" class="btn btn-sm btn-warning" title="Muter"
                                                    data-bs-toggle="modal" data-bs-target="#muteReplyModal{{ $report->id }}">
                                                <i class="bi bi-mic-mute"></i>
                                            </button>
                                            <div class="modal fade" id="muteReplyModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.reports.replies.mute-user', $report) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Muter {{ $report->reply->user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Durée</label>
                                                                    <div class="input-group">
                                                                        <input type="number" name="duration_value" class="form-control" min="1" placeholder="ex: 7" style="max-width:100px;">
                                                                        <select name="duration_unit" class="form-select">
                                                                            <option value="minutes">Minutes</option>
                                                                            <option value="hours">Heures</option>
                                                                            <option value="days" selected>Jours</option>
                                                                            <option value="weeks">Semaines</option>
                                                                            <option value="months">Mois</option>
                                                                        </select>
                                                                        <div class="input-group-text">
                                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="duration_permanent" id="permRep{{ $report->id }}" value="1" onchange="toggleDuration(this)">
                                                                            <label class="form-check-label" for="permRep{{ $report->id }}">Définitif</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Raison</label>
                                                                    <textarea name="reason" class="form-control" rows="3" maxlength="500" required placeholder="Motif du mute..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-warning">Confirmer le mute</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $replyReports->links() }}
            @endif
        </div>
    </div>
    @endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
function toggleDuration(checkbox) {
    var inputs = checkbox.closest('.input-group').querySelectorAll('input[type="number"], select');
    inputs.forEach(function(el) { el.disabled = checkbox.checked; });
}
</script>
</x-admin-layout>
