<x-app-layout>
    <x-slot name="pageTitle">Mon profil</x-slot>

    <div class="mb-4">
        <h4 class="mb-1">Mon profil</h4>
        <p style="color:var(--text-muted);font-size:.875rem;">
            Gérez vos informations personnelles et la sécurité de votre compte.
        </p>
    </div>

    {{-- ── Statistiques rapides ── --}}
    @if(\App\Models\Setting::get('feature_profile_stats', '1'))
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card p-3 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:#22c55e;">{{ $stats['mastered'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);">Maîtrisés</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card p-3 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:var(--accent);">{{ $stats['practiced'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);">Pratiqués</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card p-3 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:#fbbf24;">{{ $stats['tests'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);">Tests</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card p-3 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:#a855f7;">{{ $stats['avg_score'] }}%</div>
                <div style="font-size:.72rem;color:var(--text-muted);">Score moyen</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card p-3 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:#f97316;">{{ $stats['best_streak'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);">Meilleure série</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card p-3 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:#06b6d4;">{{ $stats['modules_used'] }}</div>
                <div style="font-size:.72rem;color:var(--text-muted);">Modules</div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4" style="max-width:700px;">

        {{-- ── Informations du profil ── --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-person-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Informations personnelles</span>
                </div>
                <div class="card-body p-4">

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer
                            </button>
                            @if (session('status') === 'profile-updated')
                                <span style="color:var(--success-color);font-size:.875rem;">
                                    <i class="bi bi-check-circle me-1"></i>Profil mis à jour !
                                </span>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- ── Double authentification (2FA) ── --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Double authentification (2FA)</span>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
                        @if(auth()->user()->hasTwoFactorAuth())
                            <i class="bi bi-check-circle-fill me-1" style="color:#22c55e;"></i>
                            La double authentification est <strong style="color:#22c55e;">activée</strong>.
                        @else
                            <i class="bi bi-shield-x me-1"></i>
                            La double authentification n'est pas activée.
                        @endif
                    </p>
                    <a href="{{ route('profile.2fa.show') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-shield-lock me-1"></i>Gérer le 2FA
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Couleur d'accentuation ── --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-palette-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Couleur d'accentuation</span>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
                        Personnalisez la couleur d'accentuation de l'interface pour votre compte uniquement.
                    </p>
                    <form method="POST" action="{{ route('profile.accent') }}">
                        @csrf
                        @method('PATCH')
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <label for="accent_color" class="form-label mb-0">Couleur</label>
                                <input type="color"
                                       id="accent_color"
                                       name="accent_color"
                                       class="form-control form-control-color"
                                       value="{{ auth()->user()->accent_color ?? setting('theme_accent', '#EFB702') }}"
                                       style="width:48px;height:36px;padding:2px;">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg me-1"></i>Appliquer
                            </button>
                            @if (auth()->user()->accent_color)
                                <a href="{{ route('profile.accent.reset') }}" class="btn btn-sm"
                                   style="color:var(--text-muted);border:1px solid var(--card-border);">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                                </a>
                            @endif
                            @if (session('status') === 'accent-updated')
                                <span style="color:var(--success-color);font-size:.875rem;">
                                    <i class="bi bi-check-circle me-1"></i>Couleur mise à jour !
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Changer le mot de passe ── --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-lock-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Changer le mot de passe</span>
                </div>
                <div class="card-body p-4">

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-lock me-1"></i>Mettre à jour
                            </button>
                            @if (session('status') === 'password-updated')
                                <span style="color:var(--success-color);font-size:.875rem;">
                                    <i class="bi bi-check-circle me-1"></i>Mot de passe mis à jour !
                                </span>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- ── Supprimer le compte ── --}}
        <div class="col-12">
            <div class="card" style="border-color:rgba(239,68,68,.3);">
                <div class="card-header d-flex align-items-center gap-2" style="border-color:rgba(239,68,68,.3);">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#ef4444;"></i>
                    <span class="fw-semibold" style="color:#ef4444;">Zone dangereuse</span>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
                        La suppression de votre compte est <strong>irréversible</strong>.
                        Tous vos modules, items et progressions seront définitivement effacés.
                    </p>

                    <button type="button"
                            class="btn btn-sm"
                            style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.3);"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteAccountModal">
                        <i class="bi bi-trash me-1"></i>Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal confirmation suppression compte --}}
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:var(--card-bg);border:1px solid rgba(239,68,68,.3);">
                <div class="modal-header" style="border-color:rgba(239,68,68,.3);">
                    <h5 class="modal-title" style="color:#ef4444;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body p-4">
                        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1rem;">
                            Cette action est <strong style="color:#ef4444;">définitive</strong>.
                            Entrez votre mot de passe pour confirmer.
                        </p>
                        <input type="password"
                               name="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Votre mot de passe"
                               required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer" style="border-color:rgba(239,68,68,.3);">
                        <button type="button" class="btn btn-sm"
                                style="color:var(--text-muted);border:1px solid var(--card-border);"
                                data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash me-1"></i>Supprimer définitivement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
