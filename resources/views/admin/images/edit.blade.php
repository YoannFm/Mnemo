<x-admin-layout>
    <x-slot name="pageTitle">Modifier l'image</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Modifier « {{ $image->name }} »</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.images.update', $image) }}" method="POST">
                @method('PATCH')
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="nameInput">Nom</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="nameInput" name="name" value="{{ old('name', $image->name) }}" required>
                    @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Image actuelle</label>
                    <div>
                        <img src="{{ $image->url() }}" class="img-fluid rounded" style="max-height:300px" alt="{{ $image->name }}">
                    </div>
                    <small class="text-muted">{{ $image->file }}</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                <a href="{{ route('admin.images.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
            <form action="{{ route('admin.images.destroy', $image) }}" method="POST" class="d-inline-block mt-2"
                  onsubmit="return confirm('Supprimer cette image ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
</x-admin-layout>
