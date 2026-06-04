@csrf

<div class="row gx-3">
    <div class="mb-3 col-12 col-md-5">
        <label class="form-label" for="nameInput">Nom</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="nameInput" name="name" value="{{ old('name', $role->name ?? '') }}" required>
        @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>

    <div class="mb-3 col-12 col-md-5">
        <label class="form-label" for="iconInput">Icône</label>
        <input type="text" class="form-control @error('icon') is-invalid @enderror"
               id="iconInput" name="icon" value="{{ old('icon', $role->icon ?? '') }}"
               placeholder="bi bi-star" aria-labelledby="iconLabel">
        @error('icon')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
        <small id="iconLabel" class="form-text">
            Vous pouvez avoir la liste des icônes disponibles sur
            <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener noreferrer">Bootstrap Icons</a>.
        </small>
    </div>

    <div class="mb-3 col-12 col-md-2">
        <label class="form-label" for="colorInput">Couleur</label>
        <input type="color" class="form-control form-control-color w-100 @error('color') is-invalid @enderror"
               id="colorInput" name="color" value="{{ old('color', $role->color ?? '#2196f3') }}" required>
        @error('color')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
</div>

<h3>Permissions</h3>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="checkbox" class="form-check-input" id="adminSwitch" name="is_admin_role" value="1"
               data-bs-toggle="collapse" data-bs-target="#permissionsGroup"
               @checked(old('is_admin_role', $role->is_admin_role ?? false))>
        <label class="form-check-label" for="adminSwitch">Administrateur</label>
    </div>
    <small class="form-text text-info">Lorsque le rôle est administrateur, il a toutes les permissions.</small>
</div>

<div id="permissionsGroup" class="{{ old('is_admin_role', $role->is_admin_role ?? false) ? 'collapse' : 'show' }}">
    <div class="card card-body mb-2 pb-0">
        <div class="row">
            <div class="col-lg-6">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="canCreateModule" name="can_create_module" value="1"
                           @checked(old('can_create_module', $role->can_create_module ?? false))>
                    <label class="form-check-label" for="canCreateModule">Créer des modules</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="canTrainOwn" name="can_train_own" value="1"
                           @checked(old('can_train_own', $role->can_train_own ?? true))>
                    <label class="form-check-label" for="canTrainOwn">S'entraîner sur ses modules</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="canTestOwn" name="can_test_own" value="1"
                           @checked(old('can_test_own', $role->can_test_own ?? true))>
                    <label class="form-check-label" for="canTestOwn">Tester ses modules</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="canAccessLibrary" name="can_access_library" value="1"
                           @checked(old('can_access_library', $role->can_access_library ?? true))>
                    <label class="form-check-label" for="canAccessLibrary">Accès à la bibliothèque</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="canTrainPublic" name="can_train_public" value="1"
                           @checked(old('can_train_public', $role->can_train_public ?? true))>
                    <label class="form-check-label" for="canTrainPublic">S'entraîner sur modules publics</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="canTestPublic" name="can_test_public" value="1"
                           @checked(old('can_test_public', $role->can_test_public ?? true))>
                    <label class="form-check-label" for="canTestPublic">Tester les modules publics</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-3"></div>
