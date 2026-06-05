<x-admin-layout>
    <x-slot name="pageTitle">Modules privés</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Modules privés</h5>
            <span class="badge bg-secondary">{{ $modules->total() }} module(s)</span>
        </div>
        <div class="card-body">

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
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Propriétaire</th>
                            <th>Items</th>
                            <th>Créé le</th>
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
                                <td>{{ $module->items_count }}</td>
                                <td>{{ $module->created_at->format('d/m/Y') }}</td>
                                <td class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('modules.show', $module) }}" class="btn btn-sm btn-outline-secondary" title="Voir" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('modules.edit', $module) }}" class="btn btn-sm btn-outline-secondary" title="Modifier" target="_blank">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('modules.export', $module) }}" class="btn btn-sm btn-outline-secondary" title="Exporter">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('modules.duplicate', $module) }}" class="d-inline"
                                          onsubmit="return confirm('Dupliquer ce module dans votre espace ?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Dupliquer">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </form>
                                    <form id="del-priv-{{ $module->id }}" method="POST"
                                          action="{{ route('admin.private-modules.destroy', $module) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                                onclick="if(confirm('Supprimer ce module ?')) document.getElementById('del-priv-{{ $module->id }}').submit()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun module privé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $modules->links() }}
        </div>
    </div>
</x-admin-layout>
