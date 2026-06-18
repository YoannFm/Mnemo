<x-admin-layout>
    <x-slot name="pageTitle">Detail du log #{{ $log->id }}</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Detail de l'action</h5></div>
        <div class="card-body">
            <h2 class="h4 text-{{ $log->getActionFormat()['color'] }}">
                <i class="bi bi-{{ $log->getActionFormat()['icon'] }}"></i>
                {{ $log->getActionMessage() }}
            </h2>
            <p>
                Par
                @if($log->user)
                    <a href="{{ route('admin.users.edit', $log->user) }}">{{ $log->user->name }}</a>
                @else
                    <em>Utilisateur supprime</em>
                @endif
                - le {{ $log->created_at->format('d/m/Y a H:i:s') }}
                &nbsp;<span class="badge bg-{{ match($log->level ?? 'info') { 'success' => 'success', 'warning' => 'warning', 'error' => 'danger', default => 'info' } }}">{{ $log->level ?? 'info' }}</span>
            </p>

            @if($log->target_type && $log->target_id)
                <p>
                    <strong>Cible :</strong> {{ $log->target_type }} #{{ $log->target_id }}
                    @if($log->target_type === 'user')
                        &mdash; <a href="{{ route('admin.users.edit', $log->target_id) }}">Voir l'utilisateur</a>
                    @elseif($log->target_type === 'module')
                        &mdash; <a href="{{ route('modules.show', $log->target_id) }}">Voir le module</a>
                    @endif
                </p>
            @endif

            @if($log->old_value || $log->new_value)
                <h5 class="mt-3">Avant / Apres</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger bg-opacity-10 text-danger fw-bold">Avant</div>
                            <div class="card-body">
                                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ $log->old_value ?? '(vide)' }}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success bg-opacity-10 text-success fw-bold">Apres</div>
                            <div class="card-body">
                                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ $log->new_value ?? '(vide)' }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($log->data)
                <h5 class="mt-3">Donnees</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Cle</th><th>Valeur</th></tr></thead>
                        <tbody>
                            @foreach($log->data as $key => $value)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mt-3">Aucune donnee supplementaire.</p>
            @endif
            <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>
    </div>
</x-admin-layout>
