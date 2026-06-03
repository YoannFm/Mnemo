<x-admin-layout>
    <x-slot name="pageTitle">Edition du rôle {{ $role->name }} (#{{ $role->id }})</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @method('PUT')
                @include('admin.roles._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy"></i> Sauvegarder
                </button>

                <a href="{{ route('admin.roles.destroy', $role) }}" class="btn btn-danger"
                   onclick="event.preventDefault(); if(confirm('Supprimer ce rôle ?')) document.getElementById('delete-role-{{ $role->id }}').submit()">
                    <i class="bi bi-trash"></i> Supprimer
                </a>
            </form>

            <form id="delete-role-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>
</x-admin-layout>
