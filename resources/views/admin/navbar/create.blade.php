<x-admin-layout>
    <x-slot name="pageTitle">Ajouter un element</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.navbar.store') }}" method="POST">
                @include('admin.navbar._form')
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </button>
                <a href="{{ route('admin.navbar.index') }}" class="btn btn-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
</x-admin-layout>
