<x-admin-layout>
    <x-slot name="pageTitle">Edition du rôle {{ $role->name }} (#{{ $role->id }})</x-slot>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @method('PUT')
        @include('admin.roles._form')

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy"></i> Sauvegarder
            </button>
            <button type="button" class="btn btn-danger"
                onclick="if(confirm('Supprimer ce role ?')) document.getElementById('delete-role-form').submit()">
                <i class="bi bi-trash"></i> Supprimer
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>

    <form id="delete-role-form" action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
</x-admin-layout>
