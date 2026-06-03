<x-admin-layout>
    <x-slot name="pageTitle">Modules publics</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Titre</th>
                            <th scope="col">Propriétaire</th>
                            <th scope="col">Items</th>
                            <th scope="col">Créé le</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modules as $module)
                            <tr>
                                <th scope="row">{{ $module->id }}</th>
                                <td>{{ $module->title }}</td>
                                <td>{{ $module->owner?->name ?? '—' }}</td>
                                <td>{{ $module->items_count }}</td>
                                <td>{{ $module->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.modules.destroy', $module) }}" class="mx-1 text-danger"
                                       title="Supprimer" data-bs-toggle="tooltip"
                                       onclick="event.preventDefault(); if(confirm('Supprimer ce module ?')) { document.getElementById('del-mod-{{ $module->id }}').submit(); }">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <form id="del-mod-{{ $module->id }}" method="POST"
                                          action="{{ route('admin.modules.destroy', $module) }}" class="d-none">
                                        @csrf @method('DELETE')
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
