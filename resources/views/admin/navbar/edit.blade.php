<x-admin-layout>
    <x-slot name="pageTitle">Modifier "{{ $navItem->label }}"</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.navbar.update', $navItem) }}" method="POST">
                @method('PUT')
                @include('admin.navbar._form')
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy"></i> Sauvegarder
                </button>
                <a href="{{ route('admin.navbar.index') }}" class="btn btn-secondary ms-2">Annuler</a>
                @if(!$navItem->is_protected)
                <button type="button" class="btn btn-danger ms-2"
                        onclick="if(confirm('Supprimer ?')) document.getElementById('del-nav-{{ $navItem->id }}').submit()">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
                @endif
            </form>

            @if(!$navItem->is_protected)
            <form id="del-nav-{{ $navItem->id }}" action="{{ route('admin.navbar.destroy', $navItem) }}" method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
            @endif
        </div>
    </div>
</x-admin-layout>
