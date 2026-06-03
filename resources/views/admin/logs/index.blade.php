<x-admin-layout>
    <x-slot name="pageTitle">Journaux d'activité</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Journal d'activité admin</h5>
            <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Effacer tous les logs ?')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Effacer</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>#</th><th>Utilisateur</th><th>Action</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <th>{{ $log->id }}</th>
                                <td>
                                    @if($log->user)
                                        <a href="{{ route('admin.users.edit', $log->user) }}">{{ $log->user->name }}</a>
                                    @else
                                        <em class="text-muted">Supprimé</em>
                                    @endif
                                </td>
                                <td class="text-{{ $log->getActionFormat()['color'] }}">
                                    <i class="bi bi-{{ $log->getActionFormat()['icon'] }}"></i>
                                    {{ $log->getActionMessage() }}
                                </td>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune activité enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</x-admin-layout>
