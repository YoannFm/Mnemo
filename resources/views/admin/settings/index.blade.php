<x-admin-layout>
    <x-slot name="pageTitle">Paramètres</x-slot>

    <div style="max-width:600px;">
        <div class="card">
            <div class="card-header"><i class="bi bi-gear me-2"></i>Paramètres du site</div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label" for="site_name">Nom du site</label>
                        <input type="text" id="site_name" name="site_name"
                               class="form-control @error('site_name') is-invalid @enderror"
                               value="{{ old('site_name', $settings['site_name']) }}"
                               maxlength="100" required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="site_description">Description</label>
                        <textarea id="site_description" name="site_description"
                                  class="form-control @error('site_description') is-invalid @enderror"
                                  rows="3" maxlength="500">{{ old('site_description', $settings['site_description']) }}</textarea>
                        @error('site_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
