<x-admin-layout>
    <x-slot name="pageTitle">Modifier - {{ $user->name }}</x-slot>

    <div class="row">

        {{-- Colonne gauche : Modifier le profil --}}
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-person-gear me-1"></i> Modifier le profil</h5>
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
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="passwordInput">Nouveau mot de passe <span class="text-muted">(facultatif)</span></label>
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
                                <option value="">-- Aucun role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                        @if($role->is_admin_role) (Admin) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Sauvegarder
                            </button>

                            @if ($user->id !== Auth::id())
                                <button type="button" class="btn btn-danger"
                                    onclick="if(confirm('Supprimer cet utilisateur ?')) { document.getElementById('delete-user-{{ $user->id }}').submit(); }">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            @endif
                        </div>
                    </form>

                    @if ($user->id !== Auth::id())
                        <form id="delete-user-{{ $user->id }}" method="POST"
                              action="{{ route('admin.users.destroy', $user) }}" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne centre : Avatar --}}
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-person-circle me-1"></i> Avatar</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height:180px;">
                    <div style="width:120px;height:120px;border-radius:50%;background:#266fd9;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#fff;font-weight:700;margin:auto;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne droite : Informations --}}
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle me-1"></i> Informations</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Inscription</label>
                        <div class="fw-semibold">{{ $user->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Derniere connexion</label>
                        <div class="fw-semibold">
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Adresse IP</label>
                        <div class="fw-semibold">{{ $user->last_login_ip ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">2FA</label>
                        <div>
                            @if ($user->two_factor_secret)
                                <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i> Active</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-shield-x me-1"></i> Desactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Modules crees</label>
                        <div class="fw-semibold">{{ $user->modules()->count() }}</div>
                    </div>

                    <form action="{{ route('admin.users.force-password-change', $user) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm w-100"
                            onclick="return confirm('Forcer le changement de mot de passe pour cet utilisateur ?')">
                            <i class="bi bi-key"></i> Forcer changement mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Section Bannissement --}}
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-slash-circle me-1"></i> Bannissement</h5>
                </div>
                <div class="card-body">
                    @if($user->is_banned)
                        <div class="alert alert-danger">
                            <i class="bi bi-slash-circle"></i> Cet utilisateur est actuellement banni.
                        </div>
                        @php $latestBan = $user->bans()->latest()->first(); @endphp
                        @if($latestBan)
                            <p><strong>Raison :</strong> {{ $latestBan->reason ?? '-' }}</p>
                            <p><strong>Banni par :</strong> {{ $latestBan->author->name ?? '?' }} le {{ $latestBan->created_at->format('d/m/Y') }}</p>
                            <form action="{{ route('admin.users.bans.destroy', [$user, $latestBan]) }}" method="POST" class="d-inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-person-check"></i> Debannir
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

        {{-- Section Logs --}}
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history me-1"></i> Activite de l'utilisateur</h5>
                </div>
                <div class="card-body p-0">
                    @if($userLogs->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune activite enregistree.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userLogs as $log)
                                        @php $fmt = $log->getActionFormat(); @endphp
                                        <tr>
                                            <td class="text-muted small">{{ $log->id }}</td>
                                            <td>
                                                <span class="badge bg-{{ $fmt['color'] }}">
                                                    <i class="bi bi-{{ $fmt['icon'] }} me-1"></i>
                                                    {{ $log->getActionMessage() }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ Str::limit($log->data ? json_encode($log->data) : '-', 80) }}
                                            </td>
                                            <td class="text-muted small text-nowrap">
                                                {{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $userLogs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
