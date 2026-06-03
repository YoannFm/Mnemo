<x-admin-layout>
    <x-slot name="pageTitle">Ajouter une image</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Uploader une image</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.images.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="nameInput">Nom</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name') }}" required>
                    @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="imageInput">Fichier image (jpg, png, gif, webp - max 2 Mo)</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="imageInput" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" required>
                    @error('image')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                <a href="{{ route('admin.images.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</x-admin-layout>
