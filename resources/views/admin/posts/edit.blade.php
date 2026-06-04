<x-admin-layout>
    <x-slot name="pageTitle">Modifier l'article</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Modifier « {{ $post->title }} »</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.posts._form')
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline-block mt-2"
                  onsubmit="return confirm('Supprimer cet article ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
</x-admin-layout>
