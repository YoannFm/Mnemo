<x-admin-layout>
    <x-slot name="pageTitle">Utilisateurs</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-1"></i> Utilisateurs
                            <span class="badge bg-secondary ms-2">{{ $users->total() }}</span>
                        </h5>
                        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   style="width:220px;"
                                   placeholder="Rechercher…"
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            @if (request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td>
                                        @if ($user->is_admin)
                                            <span class="badge bg-primary">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">Utilisateur</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                               class="btn btn-sm btn-primary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm {{ $user->is_admin ? 'btn-warning' : 'btn-outline-secondary' }}"
                                                        title="{{ $user->is_admin ? 'Retirer admin' : 'Rendre admin' }}">
                                                    <i class="bi bi-shield{{ $user->is_admin ? '-check' : '' }}"></i>
                                                </button>
                                            </form>

                                            @if ($user->id !== Auth::id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                      onsubmit="return confirm('Supprimer {{ $user->name }} ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                    <div class="card-body py-2">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
