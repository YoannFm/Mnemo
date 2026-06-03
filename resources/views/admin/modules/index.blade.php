<x-admin-layout>
    <x-slot name="pageTitle">Modules publics</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
            <i class="bi bi-collection me-2" style="color:var(--accent);"></i>Modules publics
        </h5>
        <span class="text-muted" style="font-size:.8rem;">{{ $modules->total() }} module(s)</span>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    @forelse ($modules as $module)
                        <tr>
                            <td>{{ $module->id }}</td>
                            <td>{{ $module->title }}</td>
                            <td class="text-muted">{{ $module->owner?->name ?? '—' }}</td>
                            <td>{{ $module->items_count }}</td>
                            <td class="text-muted" style="font-size:.8rem;">
                                {{ $module->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.modules.destroy', $module) }}"
                                      onsubmit="return confirm('Supprimer ce module ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
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
    </div>

    <div class="mt-3">
        {{ $modules->links() }}
    </div>
</x-admin-layout>
