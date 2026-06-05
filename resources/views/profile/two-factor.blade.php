<x-app-layout>
    <x-slot name="pageTitle">Authentification à deux facteurs</x-slot>

    <div class="mb-4">
        <h4 class="mb-1">Authentification à deux facteurs (2FA)</h4>
        <p style="color:var(--text-muted);font-size:.875rem;">
            Renforcez la sécurité de votre compte en activant la double authentification.
        </p>
    </div>

    <div style="max-width:700px;">

        @if(session('status') === '2fa-disabled')
            <div class="alert alert-warning mb-4" style="background:rgba(239,183,2,.1);border:1px solid var(--accent);color:var(--text-primary);">
                <i class="bi bi-shield-exclamation me-2" style="color:var(--accent);"></i>
                L'authentification à deux facteurs a été désactivée.
            </div>
        @endif

        {{-- ── Codes de récupération après activation ── --}}
        @if(isset($justEnabled) && $justEnabled)
            <div class="card mb-4" style="border-color:var(--accent);">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold" style="color:var(--accent);">2FA activé avec succès !</span>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1rem;">
                        <strong style="color:var(--text-primary);">Conservez ces codes de récupération en lieu sûr.</strong>
                        Chaque code ne peut être utilisé qu'une seule fois. Ils vous permettront d'accéder à votre compte si vous perdez votre application d'authentification.
                    </p>
                    <div class="p-3 mb-3" style="background:var(--body-bg);border:2px solid var(--accent);border-radius:4px;font-family:monospace;">
                        @foreach($recoveryCodes as $code)
                            <div style="color:var(--text-primary);line-height:2;">{{ $code }}</div>
                        @endforeach
                    </div>
                    <a href="{{ route('profile.2fa.show') }}" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>J'ai sauvegardé mes codes
                    </a>
                </div>
            </div>
        @endif

        {{-- ── Formulaire d'activation / confirmation ── --}}
        @if(isset($enabling) && $enabling && !isset($justEnabled))
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Configurer l'application d'authentification</span>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
                        Scannez ce QR code avec votre application d'authentification (Google Authenticator, Authy, etc.), puis saisissez le code généré pour confirmer l'activation.
                    </p>

                    <div class="mb-4 p-3 d-inline-block" style="background:#fff;border-radius:4px;">
                        {!! $qrSvg !!}
                    </div>

                    <div class="mb-4">
                        <p style="font-size:.8rem;color:var(--text-muted);">
                            Ou saisissez ce code manuellement dans votre application :
                        </p>
                        <code style="font-size:.9rem;color:var(--accent);letter-spacing:2px;">{{ $secret }}</code>
                    </div>

                    <form method="POST" action="{{ route('profile.2fa.confirm') }}">
                        @csrf
                        <input type="hidden" name="secret" value="{{ $secret }}">

                        <div class="mb-3">
                            <label for="code" class="form-label">Code de confirmation</label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   class="form-control @error('code') is-invalid @enderror"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   autofocus
                                   placeholder="000000"
                                   style="max-width:200px;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-check me-1"></i>Confirmer l'activation
                            </button>
                            <a href="{{ route('profile.2fa.show') }}" class="btn btn-sm"
                               style="color:var(--text-muted);border:1px solid var(--card-border);padding:.6rem 1.5rem;">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        {{-- ── Statut 2FA ── --}}
        @else

            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill" style="color:var(--accent);"></i>
                    <span class="fw-semibold">Statut de la double authentification</span>
                </div>
                <div class="card-body p-4">

                    @if($user->hasTwoFactorAuth())
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-check-circle-fill" style="color:#22c55e;font-size:1.2rem;"></i>
                            <span style="color:#22c55e;font-weight:600;">La double authentification est activée</span>
                        </div>
                        <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
                            Votre compte est protégé par une double authentification TOTP.
                        </p>
                        @if ($errors->has('password'))
                            <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;">{{ $errors->first('password') }}</div>
                        @endif
                        <button type="button" class="btn btn-sm"
                                style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.3);"
                                data-bs-toggle="modal" data-bs-target="#disable2faModal">
                            <i class="bi bi-shield-x me-1"></i>Désactiver le 2FA
                        </button>

                        <div class="modal fade" id="disable2faModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="background:var(--card-border);border:1px solid rgba(255,255,255,.1);">
                                    <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
                                        <h5 class="modal-title" style="color:var(--text-primary);font-size:1rem;">Désactiver la double authentification</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('profile.2fa.disable') }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body">
                                            <p style="color:var(--text-muted);font-size:.875rem;">Confirmez votre mot de passe pour désactiver le 2FA.</p>
                                            <div class="mb-3">
                                                <label class="form-label" style="color:var(--text-primary);font-size:.875rem;">Mot de passe</label>
                                                <input type="password" name="password" class="form-control" autofocus required placeholder="Votre mot de passe">
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.4);">
                                                <i class="bi bi-shield-x me-1"></i>Désactiver
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-shield-x" style="color:var(--text-muted);font-size:1.2rem;"></i>
                            <span style="color:var(--text-muted);font-weight:600;">La double authentification n'est pas activée</span>
                        </div>
                        <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
                            Activez la double authentification pour renforcer la sécurité de votre compte.
                        </p>
                        <form method="POST" action="{{ route('profile.2fa.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-plus me-1"></i>Activer le 2FA
                            </button>
                        </form>
                    @endif

                </div>
            </div>

        @endif

        <div class="mt-3">
            <a href="{{ route('profile.edit') }}" style="font-size:.875rem;color:var(--text-muted);">
                <i class="bi bi-arrow-left me-1"></i>Retour au profil
            </a>
        </div>

    </div>
</x-app-layout>
