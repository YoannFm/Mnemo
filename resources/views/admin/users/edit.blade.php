<x-admin-layout>
    <x-slot name="pageTitle">Modifier — {{ $user->name }}</x-slot>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier le profil</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @method('PUT')
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="nameInput">Nom</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="nameInput" name="name"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="emailInput">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="emailInput" name="email"
                                   value="{{ old('email', $user->email) }}">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="passwordInput">Mot de passe</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="passwordInput" name="password" placeholder="**********">
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="passwordConfirm">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="passwordConfirm"
                                   name="password_confirmation" placeholder="**********">
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_admin"
                                       id="isAdmin" value="1"
                                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isAdmin">Administrateur</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>

                        @if ($user->id !== Auth::id())
                            <a href="{{ route('admin.users.destroy', $user) }}" class="btn btn-danger"
                               onclick="event.preventDefault(); if(confirm('Supprimer cet utilisateur ?')) { document.getElementById('delete-user-{{ $user->id }}').submit(); }">
                                <i class="bi bi-trash"></i> Supprimer
                            </a>
                            <form id="delete-user-{{ $user->id }}" method="POST"
                                  action="{{ route('admin.users.destroy', $user) }}" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Inscription</label>
                        <input type="text" class="form-control"
                               value="{{ $user->created_at->format('d/m/Y à H:i') }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Modules créés</label>
                        <input type="text" class="form-control"
                               value="{{ $user->modules()->count() }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">2FA (double authentification)</label>
                        @if ($user->two_factor_secret)
                            <input type="text" class="form-control text-success"
                                   value="Activé" disabled>
                        @else
                            <input type="text" class="form-control text-danger"
                                   value="Désactivé" disabled>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bannissement</h5>
                </div>
                <div class="card-body">
                    @if($user->is_banned)
                        <div class="alert alert-danger">
                            <i class="bi bi-slash-circle"></i> Cet utilisateur est actuellement banni.
                        </div>
                        @php $latestBan = $user->bans()->latest()->first(); @endphp
                        @if($latestBan)
                            <p><strong>Raison :</strong> {{ $latestBan->reason ?? '—' }}</p>
                            <p><strong>Banni par :</strong> {{ $latestBan->author->name ?? '?' }} le {{ $latestBan->created_at->format('d/m/Y') }}</p>
                            <form action="{{ route('admin.users.bans.destroy', [$user, $latestBan]) }}" method="POST" class="d-inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-person-check"></i> Débannir
                                </button>
                            </form>
                        @endif
                    @else
                        <form action="{{ route('admin.users.bans.store', $user) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="reasonInput">Raison du bannissement</label>
                                <input type="text" class="form-control" id="reasonInput" name="reason" placeholder="Comportement abusif...">
                            </div>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Bannir cet utilisateur ?')">
                                <i class="bi bi-slash-circle"></i> Bannir l'utilisateur
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
