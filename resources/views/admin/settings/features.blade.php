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
                    <div class="d-flex justify-content-between align-items-start py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div style="flex:1;padding-right:2rem;">
                            <div style="font-weight:500;color:var(--text-primary);margin-bottom:.2rem;">{{ $meta['label'] }}</div>
                            @if(!empty($meta['description']))
                                <div style="font-size:.8rem;color:var(--text-muted);line-height:1.5;">{{ $meta['description'] }}</div>
                            @endif
                        </div>
                        <div class="form-check form-switch mb-0" style="flex-shrink:0;padding-top:.15rem;">
                            <input class="form-check-input" type="checkbox"
                                   id="{{ $key }}"
                                   name="{{ $key }}"
                                   value="1"
                                   {{ ($values[$key] ?? $meta['default']) == '1' ? 'checked' : '' }}
                                   style="width:2.5rem;height:1.25rem;cursor:pointer;">
                            <label class="form-check-label visually-hidden" for="{{ $key }}">{{ $meta['label'] }}</label>
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
