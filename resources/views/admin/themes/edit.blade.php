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
    </div>
</x-admin-layout>
