<x-admin-layout>
    <x-slot name="pageTitle">Créer un rôle</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @include('admin.roles._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Créer
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</x-admin-layout>
