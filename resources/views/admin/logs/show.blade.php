<x-admin-layout>
    <x-slot name="pageTitle">Détail du log #{{ $log->id }}</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Détail de l'action</h5></div>
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
                    <em>Utilisateur supprimé</em>
                @endif
                — le {{ $log->created_at->format('d/m/Y à H:i:s') }}
            </p>
            @if($log->data)
                <h5>Données</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Clé</th><th>Valeur</th></tr></thead>
                        <tbody>
                            @foreach($log->data as $key => $value)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Aucune donnée supplémentaire.</p>
            @endif
            <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>
    </div>
</x-admin-layout>
