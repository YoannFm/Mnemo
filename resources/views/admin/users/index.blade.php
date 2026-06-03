<x-admin-layout>
    <x-slot name="pageTitle">Utilisateurs</x-slot>

    <form class="row gx-3 align-items-center" action="{{ route('admin.users.index') }}" method="GET" role="search">
        <div class="col-md-4 col-12 mb-3">
            <label class="visually-hidden" for="searchInput">Rechercher</label>
            <div class="input-group">
                <input type="search" class="form-control" id="searchInput" name="search"
                       value="{{ request('search') }}" placeholder="Rechercher...">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Email</th>
                            <th scope="col">Rôle</th>
                            <th scope="col">Inscription</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <th scope="row">
                                    {{ $user->id }}
                                    @if ($user->is_admin)
                                        <i class="bi bi-trophy text-warning" title="Administrateur" data-bs-toggle="tooltip"></i>
                                    @endif
                                </th>
                                <td>{{ $user->name }}</td>
                                <td>
                                    {{ $user->email }}
                                    @if ($user->two_factor_secret)
                                        <i class="bi bi-shield-check text-success" data-bs-toggle="tooltip" title="2FA activé"></i>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->is_admin)
                                        <span class="badge bg-primary">Admin</span>
                                    @else
                                        <span class="badge bg-secondary">Utilisateur</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="mx-1"
                                       title="Modifier" data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
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

            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</x-admin-layout>
