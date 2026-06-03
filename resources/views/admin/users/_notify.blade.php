<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="notificationLabel">Envoyer une notification</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $route }}">
                    @csrf

                    {{-- Cible --}}
                    <div class="mb-3">
                        <label class="form-label">Destinataires</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input notify-target-radio" type="radio" name="target"
                                       id="modalTargetAll" value="all"
                                       {{ ($presetTarget ?? 'all') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label" for="modalTargetAll">Tous</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input notify-target-radio" type="radio" name="target"
                                       id="modalTargetUsers" value="users"
                                       {{ ($presetTarget ?? '') === 'users' ? 'checked' : '' }}>
                                <label class="form-check-label" for="modalTargetUsers">Utilisateurs</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input notify-target-radio" type="radio" name="target"
                                       id="modalTargetRoles" value="roles"
                                       {{ ($presetTarget ?? '') === 'roles' ? 'checked' : '' }}>
                                <label class="form-check-label" for="modalTargetRoles">Roles</label>
                            </div>
                        </div>
                    </div>

                    {{-- Utilisateurs specifiques --}}
                    <div class="mb-3 notify-section-users" style="{{ ($presetTarget ?? 'all') === 'users' ? '' : 'display:none' }}">
                        <label class="form-label">Utilisateurs</label>
                        @include('admin._user_autocomplete', [
                            'inputId'      => 'modalUserSearch',
                            'presetUsers'  => collect($notifyUsers ?? [])->whereIn('id', $presetUserIds ?? []),
                            'allUsers'     => $notifyUsers ?? [],
                        ])
                    </div>

                    {{-- Roles --}}
                    <div class="mb-3 notify-section-roles" style="{{ ($presetTarget ?? 'all') === 'roles' ? '' : 'display:none' }}">
                        <label class="form-label">Roles</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($notifyRoles ?? [] as $r)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="role_ids[]" value="{{ $r->id }}"
                                           id="modalNotifyRole{{ $r->id }}">
                                    <label class="form-check-label" for="modalNotifyRole{{ $r->id }}">
                                        @if($r->icon) <i class="{{ $r->icon }}"></i> @endif
                                        {{ $r->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Contenu --}}
                    <div class="mb-3">
                        <label class="form-label" for="notifyContent">Contenu</label>
                        <input type="text" class="form-control @error('content') is-invalid @enderror"
                               id="notifyContent" name="content" required maxlength="200">
                        @error('content')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Niveau --}}
                    <div class="mb-3">
                        <label class="form-label" for="notifyLevel">Niveau</label>
                        <select class="form-select @error('level') is-invalid @enderror" id="notifyLevel" name="level" required>
                            <option value="info">Information</option>
                            <option value="success">Succes</option>
                            <option value="warning">Avertissement</option>
                            <option value="danger">Danger</option>
                        </select>
                        @error('level')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <button class="btn btn-warning" type="submit">
                        <i class="bi bi-megaphone"></i> Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.notify-target-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelector('.notify-section-users').style.display = this.value === 'users' ? '' : 'none';
            document.querySelector('.notify-section-roles').style.display = this.value === 'roles' ? '' : 'none';
        });
    });
})();
</script>
