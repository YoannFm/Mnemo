<x-admin-layout>
    <x-slot name="pageTitle">Redirections</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Redirections URL</h5>
            <a href="{{ route('admin.redirects.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nouvelle redirection</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>#</th><th>Source</th><th>Cible</th><th>Code</th><th>Activée</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($redirects as $redirect)
                            <tr>
                                <th>{{ $redirect->id }}</th>
                                <td><code>{{ $redirect->source }}</code></td>
                                <td>{{ $redirect->target }}</td>
                                <td><span class="badge bg-secondary">{{ $redirect->type }}</span></td>
                                <td><span class="badge bg-{{ $redirect->is_enabled ? 'success' : 'danger' }}">{{ $redirect->is_enabled ? 'Oui' : 'Non' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.redirects.edit', $redirect) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.redirects.destroy', $redirect) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Supprimer cette redirection ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune redirection.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $redirects->links() }}
        </div>
    </div>
</x-admin-layout>
