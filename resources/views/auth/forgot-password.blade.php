<x-guest-layout>
    <div class="mb-4" style="font-size:.875rem;color:var(--text-muted);">
        Mot de passe oublié ? Pas de problème. Indiquez votre adresse e-mail et nous vous enverrons un lien de réinitialisation.
    </div>

    <!-- Statut session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-envelope me-2"></i>Envoyer le lien de réinitialisation
            </button>
        </div>

        <div class="text-center mt-3" style="font-size:.82rem;">
            <a href="{{ route('login') }}">Se connecter</a>
            &nbsp;·&nbsp;
            <a href="{{ route('register') }}">Créer un compte</a>
        </div>
    </form>
</x-guest-layout>
