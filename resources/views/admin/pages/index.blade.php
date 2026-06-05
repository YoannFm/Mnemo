<x-admin-layout>
    <x-slot name="pageTitle">Pages</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Titre</th>
                            <th scope="col">Slug</th>
                            <th scope="col">Active</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <th scope="row">{{ $page->id }}</th>
                                <td>{{ $page->title }}</td>
                                <td>
                                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" rel="noopener noreferrer">
                                        /p/{{ $page->slug }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $page->is_enabled ? 'success' : 'danger' }}">
                                        {{ $page->is_enabled ? 'Oui' : 'Non' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="mx-1"
                                       title="Modifier" data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST"
                                          class="d-inline-block" onsubmit="return confirm('Supprimer cette page ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link p-0 mx-1 text-danger"
                                                title="Supprimer" data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aucune page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $pages->links() }}

            <a class="btn btn-primary mt-2" href="{{ route('admin.pages.create') }}">
                <i class="bi bi-plus-lg"></i> Ajouter
            </a>
        </div>
    </div>
</x-admin-layout>
