<x-admin-layout>
    <x-slot name="pageTitle">Modules publics</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Titre</th>
                            <th scope="col">Propriétaire</th>
                            <th scope="col">Items</th>
                            <th scope="col">Créé le</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modules as $module)
                            <tr>
                                <th scope="row">{{ $module->id }}</th>
                                <td>{{ $module->title }}</td>
                                <td>
                                    @if($module->owner)
                                        <a href="{{ route('admin.users.edit', $module->owner) }}">{{ $module->owner->name }}</a>
                                    @else
                                        <em class="text-muted">-</em>
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
                                    <form id="del-mod-{{ $module->id }}" method="POST"
                                          action="{{ route('admin.modules.destroy', $module) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                                onclick="if(confirm('Supprimer ce module ?')) document.getElementById('del-mod-{{ $module->id }}').submit()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucun module public.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $modules->links() }}
        </div>
    </div>
</x-admin-layout>
