<x-admin-layout>
    <x-slot name="pageTitle">Nouvelle page</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Créer une page</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.pages.store') }}" method="POST">
                @include('admin.pages._form')
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</x-admin-layout>
