<x-admin-layout>
    <x-slot name="pageTitle">Bannissements</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Utilisateurs bannis</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Utilisateur</th>
                            <th>Banni par</th>
                            <th>Raison</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bans as $ban)
                            <tr>
                                <th>{{ $ban->id }}</th>
                                <td>
                                    <a href="{{ route('admin.users.edit', $ban->user) }}">{{ $ban->user->name }}</a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $ban->author) }}">{{ $ban->author->name }}</a>
                                </td>
                                <td>{{ $ban->reason ?? '—' }}</td>
                                <td>{{ $ban->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form action="{{ route('admin.users.bans.destroy', [$ban->user, $ban]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Débannir cet utilisateur ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-person-check"></i> Débannir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun bannissement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bans->links() }}
        </div>
    </div>
</x-admin-layout>
