<x-admin-layout>
    <x-slot name="pageTitle">Paramètres</x-slot>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Paramètres du site</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="site_name">Nom du site</label>
                            <input type="text" id="site_name" name="site_name"
                                   class="form-control @error('site_name') is-invalid @enderror"
                                   value="{{ old('site_name', $settings['site_name']) }}"
                                   maxlength="100" required>
                            @error('site_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="site_description">Description</label>
                            <textarea id="site_description" name="site_description"
                                      class="form-control @error('site_description') is-invalid @enderror"
                                      rows="3" maxlength="500">{{ old('site_description', $settings['site_description']) }}</textarea>
                            @error('site_description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
