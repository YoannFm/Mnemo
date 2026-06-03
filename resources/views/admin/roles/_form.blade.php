@csrf
<div class="mb-3">
    <label class="form-label" for="nameInput">Nom</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $role->name ?? '') }}" required>
    @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="row">
    <div class="mb-3 col-md-4">
        <label class="form-label" for="colorInput">Couleur</label>
        <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" id="colorInput" name="color" value="{{ old('color', $role->color ?? '#2196f3') }}" required>
        @error('color')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
    <div class="mb-3 col-md-4">
        <label class="form-label" for="powerInput">Puissance</label>
        <input type="number" class="form-control @error('power') is-invalid @enderror" id="powerInput" name="power" value="{{ old('power', $role->power ?? 0) }}" min="0" required>
        @error('power')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
    <div class="mb-3 col-md-4 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="adminSwitch" name="is_admin_role" value="1" @checked(old('is_admin_role', $role->is_admin_role ?? false))>
            <label class="form-check-label" for="adminSwitch">Rôle admin</label>
        </div>
    </div>
</div>
