<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="notificationLabel">Envoyer une notification</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <h3>{{ ($all ?? false) ? 'Envoyer une notification a tous les utilisateurs' : 'Envoyer une notification a cet utilisateur' }}</h3>

                <form method="POST" action="{{ $route }}">
                    @csrf
                    @if($all ?? false)
                        <input type="hidden" name="target" value="all">
                    @else
                        <input type="hidden" name="target" value="user">
                        <input type="hidden" name="user_id" value="{{ $userId ?? '' }}">
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="contentInput">Contenu</label>
                        <input type="text" class="form-control @error('content') is-invalid @enderror"
                               id="contentInput" name="content" required maxlength="200">
                        @error('content')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="levelSelect">Niveau</label>
                        <select class="form-select @error('level') is-invalid @enderror" id="levelSelect" name="level" required>
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
