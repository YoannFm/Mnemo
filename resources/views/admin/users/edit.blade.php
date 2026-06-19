<x-admin-layout>
    <x-slot name="pageTitle">Edition de l'utilisateur {{ $user->name }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('admin.users.export', $user) }}" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> Telecharger les donnees
        </a>
    </div>

    {{-- Alerte bannissement --}}
    @if($user->is_banned)
        @php $latestBan = $user->bans()->latest()->first(); @endphp
        <div class="alert alert-warning shadow" role="alert">
            <h5><i class="bi bi-exclamation-triangle"></i> Cet utilisateur est banni</h5>
            <ul class="mb-2">
                @if($latestBan)
                    <li>Banni par : {{ $latestBan->author->name ?? '?' }}</li>
                    <li>Raison : {{ $latestBan->reason ?? '-' }}</li>
                    <li>Date : {{ $latestBan->created_at->format('d/m/Y à H:i') }}</li>
                @endif
            </ul>
            @if($latestBan)
                <form method="POST" action="{{ route('admin.users.bans.destroy', [$user, $latestBan]) }}" class="d-inline">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-slash-circle"></i> Debannir
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div class="row">

        {{-- Colonne gauche : Modifier le profil --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier le profil</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @method('PUT')
                        @csrf

                        <div class="row">
                            <div class="col-12 col-sm-9">
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
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 col-sm-3 text-center d-flex align-items-center justify-content-center">
                                <div style="width:80px;height:80px;border-radius:50%;background:#266fd9;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;font-weight:700;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="passwordInput">Mot de passe</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="passwordInput" name="password" placeholder="Laisser vide pour ne pas changer">
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="passwordConfirm">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="passwordConfirm"
                                   name="password_confirmation" placeholder="Laisser vide pour ne pas changer">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="roleSelect">Role</label>
                            <select class="form-select @error('role_id') is-invalid @enderror" id="roleSelect" name="role_id">
                                <option value="">- Aucun role -</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}@if($role->is_admin_role) (Admin)@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Sauvegarder
                        </button>

                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#notificationModal">
                            <i class="bi bi-megaphone"></i> Envoyer une notification
                        </button>

                        @if ($user->id !== Auth::id() && !$user->is_banned)
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#banModal">
                                <i class="bi bi-slash-circle"></i> Bannir
                            </button>
                        @endif

                        @if ($user->id !== Auth::id())
                            <button type="button" class="btn btn-danger"
                                onclick="if(confirm('Supprimer definitivement cet utilisateur ?')) document.getElementById('delete-form-{{ $user->id }}').submit()">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        @endif
                    </form>

                    @if ($user->id !== Auth::id())
                        <form id="delete-form-{{ $user->id }}" method="POST"
                              action="{{ route('admin.users.destroy', $user) }}" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne droite : Informations --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de l'utilisateur</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="registerInput">Inscrit le</label>
                        <input type="text" class="form-control" id="registerInput"
                               value="{{ $user->created_at->format('d/m/Y à H:i') }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="lastLoginInput">Derniere connexion</label>
                        <input type="text" class="form-control" id="lastLoginInput"
                               value="{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Statut du compte</label>
                        @if($user->deactivated_at)
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" class="form-control text-danger" value="Désactivé le {{ $user->deactivated_at->format('d/m/Y à H:i') }}" disabled>
                                <form action="{{ route('admin.users.reactivate', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">
                                        <i class="bi bi-person-check me-1"></i>Réactiver
                                    </button>
                                </form>
                            </div>
                        @else
                            <input type="text" class="form-control text-success" value="Actif" disabled>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="2faInput">Authentification a deux facteurs</label>
                        @if($user->two_factor_secret)
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" class="form-control text-success" id="2faInput" value="Active" disabled>
                                <form action="{{ route('admin.users.disable-2fa', $user) }}" method="POST" onsubmit="return confirm('Désactiver la 2FA de cet utilisateur ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                        <i class="bi bi-shield-x me-1"></i>Désactiver
                                    </button>
                                </form>
                            </div>
                        @else
                            <input type="text" class="form-control text-danger" id="2faInput" value="Non active" disabled>
                        @endif
                    </div>

                    <form action="{{ route('admin.users.force-password-change', $user) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="forcePasswordInput">Changement de mot de passe</label>
                            @if($user->force_password_change)
                                <input type="text" class="form-control" id="forcePasswordInput"
                                       value="Changement force en attente" disabled>
                            @else
                                <div class="input-group">
                                    <input type="text" class="form-control" id="forcePasswordInput"
                                           value="{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Inconnu' }}" disabled>
                                    <button class="btn btn-outline-danger" type="submit"
                                        onclick="return confirm('Forcer le changement de mot de passe ?')">
                                        Forcer le changement
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>

                    <div class="mb-3">
                        <label class="form-label" for="ipInput">Adresse IP</label>
                        <input type="text" class="form-control" id="ipInput"
                               value="{{ $user->last_login_ip ?? 'Inconnue' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Modules crees</label>
                        <input type="text" class="form-control" value="{{ $user->modules()->count() }}" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Logs --}}
    @if(!$userLogs->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Action</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userLogs as $log)
                                @php $fmt = method_exists($log, 'getActionFormat') ? $log->getActionFormat() : ['color'=>'secondary','icon'=>'circle']; @endphp
                                <tr>
                                    <th scope="row">{{ $log->id }}</th>
                                    <td>
                                        <i class="text-{{ $fmt['color'] }} bi bi-{{ $fmt['icon'] }}"></i>
                                        {{ method_exists($log, 'getActionMessage') ? $log->getActionMessage() : ($log->action ?? '-') }}
                                    </td>
                                    <td>{{ $log->created_at ? $log->created_at->format('d/m/Y à H:i') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.logs.show', $log) }}" class="mx-1" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $userLogs->links() }}
            </div>
        </div>
    @endif

    {{-- Modal bannissement --}}
    @if(!$user->is_banned && $user->id !== Auth::id())
        <div class="modal fade" id="banModal" tabindex="-1" role="dialog" aria-labelledby="banLabel" aria-modal="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="banLabel">Bannir {{ $user->name }}</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p>Cette action empechera l'utilisateur de se connecter. Vous pourrez le debannir plus tard.</p>

                        <form method="POST" action="{{ route('admin.users.bans.store', $user) }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="reasonInput">Raison</label>
                                <input type="text" class="form-control @error('reason') is-invalid @enderror"
                                       id="reasonInput" name="reason" required placeholder="Comportement abusif...">
                                @error('reason')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                            <button class="btn btn-danger" type="submit">
                                <i class="bi bi-slash-circle"></i> Bannir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('admin.users._notify', [
        'route'          => route('admin.notifications.store'),
        'notifyUsers'    => $allUsers,
        'notifyRoles'    => $roles,
        'presetTarget'   => 'users',
        'presetUserIds'  => [$user->id],
    ])

</x-admin-layout>
