<x-admin-layout>
    <x-slot name="pageTitle">Modifier le theme</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Modifier "{{ $theme->name }}"</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.themes.update', $theme) }}" method="POST">
                @method('PUT')
                @include('admin.themes._form')
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                <a href="{{ route('admin.themes.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <form action="{{ route('admin.themes.duplicate', $theme) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-copy me-1"></i> Dupliquer
                </button>
            </form>
            @if(!$theme->is_active)
                <form action="{{ route('admin.themes.destroy', $theme) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce thème ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Supprimer
                    </button>
                </form>
            @else
                <span class="text-muted small"><i class="bi bi-lock me-1"></i>Thème actif — impossible à supprimer</span>
            @endif
        </div>
    </div>
</x-admin-layout>
