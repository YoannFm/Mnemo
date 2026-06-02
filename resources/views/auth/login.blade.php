<x-guest-layout>

    {{-- Message de statut de session (ex : après réinitialisation de mot de passe) --}}
    @if (session('status'))
        <div class="alert alert-success mb-3" style="font-size:.85rem;">
            {{ session('status') }}
        </div>
    @endif

    <h5 class="fw-bold mb-1">Connexion</h5>
    <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1.5rem;">
        Connectez-vous pour accéder à vos modules.
    </p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Adresse e-mail --}}
        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input id="email"
                   type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username"
                   placeholder="votre@email.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Mot de passe --}}
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password"
                   type="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required
                   autocomplete="current-password"
                   placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Se souvenir de moi --}}
        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label" style="font-size:.85rem;color:var(--text-muted);">
                Se souvenir de moi
            </label>
        </div>

        {{-- Bouton connexion --}}
        <button type="submit" class="btn btn-primary w-100 mb-3">
            Se connecter
        </button>

        {{-- Liens secondaires --}}
        <div class="d-flex justify-content-between" style="font-size:.82rem;">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color:var(--text-muted);">
                    Mot de passe oublié ?
                </a>
            @endif
            <a href="{{ route('register') }}">Créer un compte</a>
        </div>

    </form>

</x-guest-layout>
