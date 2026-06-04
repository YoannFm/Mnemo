<x-admin-layout>
    <x-slot name="pageTitle">Utilisateurs</x-slot>

    <form class="row gx-3 align-items-center mb-3" action="{{ route('admin.users.index') }}" method="GET" role="search">
        <div class="col-md-4 col-12 mb-2 mb-md-0">
            <label class="visually-hidden" for="searchInput">Rechercher</label>
            <div class="input-group">
                <input type="search" class="form-control" id="searchInput" name="search"
                       value="{{ request('search') }}" placeholder="Rechercher...">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
        <div class="col-md-3 col-12">
            <select name="role_id" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
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
                            <th scope="col">Role</th>
                            <th scope="col">Inscription</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <th scope="row">
                                    {{ $user->id }}
                                    @if($user->is_admin)
                                        <i class="bi bi-trophy text-warning" title="Administrateur" data-bs-toggle="tooltip"></i>
                                    @endif
                                    @if($user->is_banned)
                                        <i class="bi bi-slash-circle text-danger" title="Banni" data-bs-toggle="tooltip"></i>
                                    @endif
                                </th>
                                <td>{{ $user->name }}</td>
                                <td>
                                    {{ $user->email }}
                                    @if($user->email_verified_at)
                                        <i class="bi bi-envelope-check text-info" data-bs-toggle="tooltip" title="Email verifie"></i>
                                    @endif
                                    @if($user->two_factor_secret)
                                        <i class="bi bi-shield-check text-success" data-bs-toggle="tooltip" title="2FA active"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role)
                                        <span class="badge" style="{{ $user->role->getBadgeStyle() }}">
                                            @if($user->role->icon) <i class="{{ $user->role->icon }}"></i> @endif
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="mx-1"
                                       title="Modifier" data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('admin.users.export', $user) }}" class="mx-1"
                                       title="Telecharger les donnees" data-bs-toggle="tooltip">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->withQueryString()->links() }}

            <div class="mt-3 d-flex gap-2">
                <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </a>
                <a class="btn btn-outline-secondary" href="{{ route('admin.users.import') }}">
                    <i class="bi bi-upload"></i> Importer
                </a>
                <a class="btn btn-outline-success" href="{{ route('admin.users.export-all') }}">
                    <i class="bi bi-file-earmark-zip"></i> Exporter tout
                </a>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <i class="bi bi-megaphone"></i> Envoyer une notification
                </button>
            </div>
        </div>
    </div>

    @include('admin.users._notify', [
        'route'        => route('admin.notifications.store'),
        'notifyUsers'  => $allUsers,
        'notifyRoles'  => $roles,
    ])

</x-admin-layout>
