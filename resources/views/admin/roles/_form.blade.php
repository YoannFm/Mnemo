@csrf

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label" for="nameInput">Nom</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="nameInput" name="name" value="{{ old('name', $role->name ?? '') }}" required>
        @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="iconInput">Icone</label>
        <input type="text" class="form-control @error('icon') is-invalid @enderror"
               id="iconInput" name="icon" value="{{ old('icon', $role->icon ?? 'bi bi-person') }}"
               placeholder="bi bi-star">
        <div class="form-text">
            Liste des icones disponibles sur <a href="https://icons.getbootstrap.com" target="_blank">Bootstrap Icons</a>.
        </div>
        @error('icon')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label" for="colorInput">Couleur</label>
        <input type="color" class="form-control form-control-color w-100 @error('color') is-invalid @enderror"
               id="colorInput" name="color" value="{{ old('color', $role->color ?? '#2196f3') }}" required>
        @error('color')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
</div>

<h6 class="mb-3">Permissions</h6>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="checkbox" class="form-check-input" id="adminSwitch" name="is_admin_role" value="1"
               @checked(old('is_admin_role', $role->is_admin_role ?? false))
               onchange="document.getElementById('perms-section').style.display = this.checked ? 'none' : 'block'">
        <label class="form-check-label" for="adminSwitch">Administrateur</label>
    </div>
    <div class="form-text text-primary">
        Lorsque le role est administrateur, il a toutes les permissions.
    </div>
</div>

<div id="perms-section" style="display: {{ old('is_admin_role', $role->is_admin_role ?? false) ? 'none' : 'block' }};">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="canCreateModule" name="can_create_module" value="1"
                       @checked(old('can_create_module', $role->can_create_module ?? false))>
                <label class="form-check-label" for="canCreateModule">Creer des modules</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="canTrainOwn" name="can_train_own" value="1"
                       @checked(old('can_train_own', $role->can_train_own ?? true))>
                <label class="form-check-label" for="canTrainOwn">S'entrainer sur ses modules</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="canTestOwn" name="can_test_own" value="1"
                       @checked(old('can_test_own', $role->can_test_own ?? true))>
                <label class="form-check-label" for="canTestOwn">Tester ses modules</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="canAccessLibrary" name="can_access_library" value="1"
                       @checked(old('can_access_library', $role->can_access_library ?? true))>
                <label class="form-check-label" for="canAccessLibrary">Acces a la bibliotheque</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="canTrainPublic" name="can_train_public" value="1"
                       @checked(old('can_train_public', $role->can_train_public ?? true))>
                <label class="form-check-label" for="canTrainPublic">S'entrainer sur modules publics</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="canTestPublic" name="can_test_public" value="1"
                       @checked(old('can_test_public', $role->can_test_public ?? true))>
                <label class="form-check-label" for="canTestPublic">Tester les modules publics</label>
            </div>
        </div>
    </div>
</div>

<div class="mt-3"></div>
