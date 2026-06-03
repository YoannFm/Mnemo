<x-admin-layout>
    <x-slot name="pageTitle">Rôles</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Liste des rôles</h5>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nouveau rôle
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Couleur</th>
                            <th>Puissance</th>
                            <th>Admin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <th>{{ $role->id }}</th>
                                <td>
                                    <span class="badge" style="{{ $role->getBadgeStyle() }}">
                                        {{ $role->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $role->color }};">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                    {{ $role->color }}
                                </td>
                                <td>{{ $role->power }}</td>
                                <td>
                                    @if($role->is_admin_role)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-trophy"></i> Admin</span>
                                    @else
                                        <span class="badge bg-secondary">Non</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Supprimer ce rôle ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
