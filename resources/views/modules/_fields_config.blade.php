{{-- Section configuration des champs actifs pour les items du module --}}
<div class="mb-4 p-3" style="border:1px solid var(--card-border);border-radius:8px;">
    <label class="form-label fw-semibold d-block" style="font-size:.875rem;">
        <i class="bi bi-sliders me-1" style="color:var(--accent);"></i>
        Champs actifs pour les items
        <span class="ms-1 text-danger" style="font-size:.75rem;">(au moins 2 requis)</span>
    </label>
    <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">
        Sélectionnez les informations que chaque item de ce module doit contenir.
        Les champs activés seront obligatoires à la saisie.
    </p>

    @error('fields')
        <div class="alert alert-danger py-2 px-3 mb-2" style="font-size:.8rem;">{{ $message }}</div>
    @enderror

    <div class="d-flex flex-wrap gap-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   id="field_name_fr" name="field_name_fr" value="1"
                   {{ old('field_name_fr', $module->field_name_fr ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="field_name_fr" style="font-size:.875rem;">
                <i class="bi bi-type me-1"></i> Nom FR
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   id="field_name_alt" name="field_name_alt" value="1"
                   {{ old('field_name_alt', $module->field_name_alt ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="field_name_alt" style="font-size:.875rem;">
                <i class="bi bi-translate me-1"></i> Nom alternatif
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   id="field_photo" name="field_photo" value="1"
                   {{ old('field_photo', $module->field_photo ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="field_photo" style="font-size:.875rem;">
                <i class="bi bi-image me-1"></i> Photo
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   id="field_function" name="field_function" value="1"
                   {{ old('field_function', $module->field_function ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="field_function" style="font-size:.875rem;">
                <i class="bi bi-card-text me-1"></i> Description / Fonction
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   id="field_audio" name="field_audio" value="1"
                   {{ old('field_audio', $module->field_audio ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="field_audio" style="font-size:.875rem;">
                <i class="bi bi-music-note me-1"></i> Son / Audio
            </label>
        </div>
    </div>
</div>
