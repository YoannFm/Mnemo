<x-admin-layout>
    <x-slot name="pageTitle">Journaux d'activite</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Journal d'activite admin</h5>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.logs.purge') }}" method="POST" onsubmit="return confirm('Supprimer les logs de plus de 30 jours ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-trash"></i> Purger &gt; 30 jours</button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportLogsModal">
                    <i class="bi bi-file-earmark-zip me-1"></i>Exporter (ZIP)
                </button>
            </div>
        </div>
        <div class="card-body">

            {{-- Filtres --}}
            <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-2 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher (action...)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Date debut">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Date fin">
                </div>
                <div class="col-md-2">
                    <select name="level" class="form-select form-select-sm">
                        <option value="">Tous niveaux</option>
                        <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="success" {{ request('level') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="target_type" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        @foreach($targetTypes as $tt)
                            <option value="{{ $tt }}" {{ request('target_type') === $tt ? 'selected' : '' }}>{{ $tt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tous les utilisateurs</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ request('user_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrer</button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Niveau</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($log->user)
                                        <a href="{{ route('admin.users.edit', $log->user) }}">{{ $log->user->name }}</a>
                                    @else
                                        <em class="text-muted">Supprime</em>
                                    @endif
                                </td>
                                <td class="text-{{ $log->getActionFormat()['color'] }}">
                                    <i class="bi bi-{{ $log->getActionFormat()['icon'] }}"></i>
                                    {{ $log->getActionMessage() }}
                                </td>
                                <td>
                                    @if($log->data)
                                        @if(!empty($log->data['download_url']))
                                            <span class="small">{{ $log->data['fichier'] ?? '' }}</span>
                                            <a href="{{ $log->data['download_url'] }}" class="btn btn-sm btn-outline-secondary ms-1" title="Retélécharger">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @else
                                            <span class="small text-muted">{{ Str::limit(json_encode($log->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 80) }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $levelColor = match($log->level ?? 'info') {
                                            'success' => 'success',
                                            'warning' => 'warning',
                                            'error'   => 'danger',
                                            default   => 'info',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $levelColor }}">{{ $log->level ?? 'info' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune activite enregistree.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</x-admin-layout>

{{-- Modal export logs --}}
<div class="modal fade" id="exportLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.logs.export') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-zip me-2"></i>Exporter les logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe admin</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Votre mot de passe" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-download me-1"></i>Télécharger</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->has('password'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('exportLogsModal')).show();
});
</script>
@endif
