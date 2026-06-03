<x-admin-layout>
    <x-slot name="pageTitle">Envoyer une notification</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-megaphone me-2"></i>Envoyer une notification</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notifications.store') }}">
                        @csrf

                        {{-- Cible --}}
                        <div class="mb-3">
                            <label class="form-label">Destinataires</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input create-target-radio" type="radio" name="target"
                                           id="createTargetAll" value="all"
                                           {{ old('target', 'all') === 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="createTargetAll">
                                        <i class="bi bi-people-fill me-1"></i> Tous
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input create-target-radio" type="radio" name="target"
                                           id="createTargetUsers" value="users"
                                           {{ old('target') === 'users' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="createTargetUsers">
                                        <i class="bi bi-person-fill me-1"></i> Utilisateurs specifiques
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input create-target-radio" type="radio" name="target"
                                           id="createTargetRoles" value="roles"
                                           {{ old('target') === 'roles' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="createTargetRoles">
                                        <i class="bi bi-shield-fill me-1"></i> Par role
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Utilisateurs specifiques --}}
                        <div class="mb-3 create-section-users" style="{{ old('target') === 'users' ? '' : 'display:none' }}">
                            <label class="form-label">Utilisateurs</label>
                            @include('admin._user_autocomplete', [
                                'inputId'     => 'createUserSearch',
                                'allUsers'    => $users,
                                'presetUsers' => collect(),
                            ])
                            @error('user_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Roles --}}
                        <div class="mb-3 create-section-roles" style="{{ old('target') === 'roles' ? '' : 'display:none' }}">
                            <label class="form-label">Roles</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="role_ids[]" value="{{ $role->id }}"
                                               id="createRole{{ $role->id }}"
                                               {{ in_array($role->id, old('role_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="createRole{{ $role->id }}">
                                            @if($role->icon) <i class="{{ $role->icon }}"></i> @endif
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('role_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Contenu --}}
                        <div class="mb-3">
                            <label class="form-label" for="contentInput">Contenu *</label>
                            <input type="text" class="form-control @error('content') is-invalid @enderror"
                                   id="contentInput" name="content"
                                   value="{{ old('content') }}" required maxlength="200">
                            @error('content')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Niveau --}}
                        <div class="mb-3">
                            <label class="form-label" for="levelSelect">Niveau</label>
                            <select class="form-select @error('level') is-invalid @enderror" id="levelSelect" name="level">
                                <option value="info"    {{ old('level') === 'info'    ? 'selected' : '' }}>Information</option>
                                <option value="success" {{ old('level') === 'success' ? 'selected' : '' }}>Succes</option>
                                <option value="warning" {{ old('level') === 'warning' ? 'selected' : '' }}>Avertissement</option>
                                <option value="danger"  {{ old('level') === 'danger'  ? 'selected' : '' }}>Danger</option>
                            </select>
                            @error('level')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-megaphone"></i> Envoyer
                            </button>
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.create-target-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelector('.create-section-users').style.display = this.value === 'users' ? '' : 'none';
            document.querySelector('.create-section-roles').style.display = this.value === 'roles' ? '' : 'none';
        });
    });
    </script>
</x-admin-layout>
