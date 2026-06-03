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

                        <div class="mb-3">
                            <label class="form-label" for="titleInput">Titre *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="titleInput" name="title" value="{{ old('title') }}" required maxlength="200">
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="messageInput">Message <span class="text-muted">(facultatif)</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="messageInput" name="message" rows="4" maxlength="1000">{{ old('message') }}</textarea>
                            @error('message')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="typeSelect">Type</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="typeSelect" name="type">
                                <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Information</option>
                                <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Succes</option>
                                <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Avertissement</option>
                                <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Danger</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cible</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target" id="targetAll"
                                           value="all" {{ old('target', 'all') === 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="targetAll">
                                        <i class="bi bi-people-fill me-1"></i> Tous les utilisateurs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target" id="targetUser"
                                           value="user" {{ old('target') === 'user' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="targetUser">
                                        <i class="bi bi-person-fill me-1"></i> Utilisateur specifique
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="userSelectWrapper">
                            <label class="form-label" for="userSelect">Utilisateur</label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="userSelect" name="user_id">
                                <option value="">- Choisir un utilisateur -</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Envoyer
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
    document.addEventListener('DOMContentLoaded', function() {
        var radios = document.querySelectorAll('input[name="target"]');
        var wrapper = document.getElementById('userSelectWrapper');
        function toggleWrapper() {
            var val = document.querySelector('input[name="target"]:checked').value;
            wrapper.classList.toggle('d-none', val !== 'user');
        }
        radios.forEach(function(r) { r.addEventListener('change', toggleWrapper); });
        toggleWrapper();
    });
    </script>
</x-admin-layout>
