<x-admin-layout>
    <x-slot name="pageTitle">Articles</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Articles / Actualités</h5>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nouvel article</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>#</th><th>Titre</th><th>Slug</th><th>Auteur</th><th>Publié le</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <th>{{ $post->id }}</th>
                                <td>{{ $post->title }}</td>
                                <td>{{ $post->slug }}</td>
                                <td>{{ $post->author->name }}</td>
                                <td>{{ $post->published_at ? $post->published_at->format('d/m/Y') : '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Supprimer cet article ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun article.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $posts->links() }}
        </div>
    </div>
</x-admin-layout>
