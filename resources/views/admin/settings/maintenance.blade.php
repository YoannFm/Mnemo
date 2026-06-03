<x-admin-layout>
    <x-slot name="pageTitle">Paramètres — Maintenance</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Maintenance</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.maintenance.update') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="maintenance_message">Message de maintenance</label>
                    <textarea id="maintenance_message" name="maintenance_message"
                              class="form-control @error('maintenance_message') is-invalid @enderror"
                              rows="8">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                    <div class="form-text">Le contenu HTML de ce message sera affiché sur la page de maintenance.</div>
                    @error('maintenance_message')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenance_enabled"
                               name="maintenance_enabled" value="1"
                               {{ old('maintenance_enabled', $settings['maintenance_enabled']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="maintenance_enabled">
                            Activer la maintenance
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenance_all"
                               name="maintenance_all" value="1"
                               {{ old('maintenance_all', $settings['maintenance_all']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="maintenance_all">
                            Appliquer sur tout le site (y compris les admins)
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
