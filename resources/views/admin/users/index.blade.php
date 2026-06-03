<x-admin-layout>
    <x-slot name="pageTitle">Utilisateurs</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
            <i class="bi bi-people me-2" style="color:var(--accent);"></i>Utilisateurs
        </h5>
        <span class="text-muted" style="font-size:.8rem;">{{ $users->total() }} utilisateur(s)</span>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
        <div class="input-group" style="max-width:400px;">
            <input type="text" name="search" class="form-control"
                   placeholder="Rechercher par nom ou email..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            @if (request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                @if ($user->is_admin)
                                    <span class="badge-admin">Admin</span>
                                @else
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:.8rem;">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm {{ $user->is_admin ? 'btn-outline-primary' : 'btn-primary' }}"
                                                title="{{ $user->is_admin ? 'Retirer admin' : 'Rendre admin' }}">
                                            <i class="bi bi-shield{{ $user->is_admin ? '-check' : '' }}"></i>
                                        </button>
                                    </form>

                                    @if ($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
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
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</x-admin-layout>
