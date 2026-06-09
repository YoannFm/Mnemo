<x-admin-layout>
    <x-slot name="pageTitle">Paramètres - Fonctionnalités</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Fonctionnalités</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <p class="text-muted mb-4" style="font-size:.875rem;">
                Activez ou désactivez les fonctionnalités de l'application. Les fonctionnalités désactivées seront masquées pour tous les utilisateurs.
            </p>

            <form method="POST" action="{{ route('admin.settings.features.update') }}">
                @csrf

                @foreach($features as $key => $meta)
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="{{ $key }}"
                                   name="{{ $key }}"
                                   value="1"
                                   {{ ($values[$key] ?? $meta['default']) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $key }}">
                                {{ $meta['label'] }}
                            </label>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
