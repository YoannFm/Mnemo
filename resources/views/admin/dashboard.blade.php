<x-admin-layout>
    <x-slot name="pageTitle">Tableau de bord</x-slot>

    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Utilisateurs</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['users'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Modules publics</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-collection"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['modules'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Items</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-card-list"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['items'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Tests réalisés</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['tests'] }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Derniers inscrits</h5>
                </div>
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
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <th scope="row">{{ $user->id }}</th>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if ($user->role)
                                                <span class="badge" style="{{ $user->role->getBadgeStyle() }}">
                                                    @if($user->role->icon)<i class="{{ $user->role->icon }} me-1"></i>@endif{{ $user->role->name }}
                                                </span>
                                            @else
                                                <span class="badge" style="background-color:#6c757d;color:#fff;">Aucun</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="mx-1" title="Modifier" data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Aucun utilisateur.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a class="btn btn-primary" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people"></i> Voir tous les utilisateurs
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
