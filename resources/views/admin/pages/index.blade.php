<x-admin-layout>
    <x-slot name="pageTitle">Pages</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Pages statiques</h5>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nouvelle page</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>#</th><th>Titre</th><th>Slug</th><th>Publié</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <th>{{ $page->id }}</th>
                                <td>{{ $page->title }}</td>
                                <td>{{ $page->slug }}</td>
                                <td><span class="badge bg-{{ $page->is_published ? 'success' : 'danger' }}">{{ $page->is_published ? 'Oui' : 'Non' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Supprimer cette page ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune page.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $pages->links() }}
        </div>
    </div>
</x-admin-layout>
