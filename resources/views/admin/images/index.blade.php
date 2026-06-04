<x-admin-layout>
    <x-slot name="pageTitle">Images</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Médiathèque</h5>
            <a href="{{ route('admin.images.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-upload"></i> Ajouter une image</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>#</th><th>Aperçu</th><th>Nom</th><th>Fichier</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($images as $image)
                            <tr>
                                <th>{{ $image->id }}</th>
                                <td><img src="{{ $image->url() }}" style="max-height:50px;max-width:100px;" class="rounded" alt="{{ $image->name }}"></td>
                                <td>{{ $image->name }}</td>
                                <td><a href="{{ $image->url() }}" target="_blank" rel="noopener noreferrer">{{ $image->file }}</a></td>
                                <td>
                                    <a href="{{ route('admin.images.edit', $image) }}" class="mx-1" title="Modifier"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.images.destroy', $image) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Supprimer cette image ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link p-0 mx-1 text-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune image.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $images->links() }}
        </div>
    </div>
</x-admin-layout>
