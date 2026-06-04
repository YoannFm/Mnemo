<x-admin-layout>
    <x-slot name="pageTitle">Signalements</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Signalements de commentaires</h5></div>
        <div class="card-body">
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
                        @forelse($reports as $report)
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
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer sanctionné">
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
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">Aucun signalement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $reports->links() }}
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
</x-admin-layout>
