<x-admin-layout>
    <x-slot name="pageTitle">Modules prives</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Modules prives (is_public = false)</h5>
            <span class="badge bg-secondary">{{ $modules->total() }} module(s)</span>
        </div>
        <div class="card-body">

            {{-- Filtres --}}
            <form method="GET" action="{{ route('admin.private-modules.index') }}" class="row g-2 mb-4">
                <div class="col-md-4">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tous les utilisateurs</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Filtrer</button>
                    <a href="{{ route('admin.private-modules.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Proprietaire</th>
                            <th>Items</th>
                            <th>Cree le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                            <tr>
                                <td>{{ $module->id }}</td>
                                <td>{{ $module->title }}</td>
                                <td>
                                    @if($module->owner)
                                        <a href="{{ route('admin.users.edit', $module->owner) }}">{{ $module->owner->name }}</a>
                                    @else
                                        <em class="text-muted">Inconnu</em>
                                    @endif
                                </td>
                                <td>{{ $module->items()->count() }}</td>
                                <td>{{ $module->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <form action="{{ route('admin.private-modules.destroy', $module) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce module ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun module prive.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $modules->links() }}
        </div>
    </div>
</x-admin-layout>
