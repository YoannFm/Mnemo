<x-guest-layout>

    <h5 class="fw-bold mb-1">Créer un compte</h5>
    <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1.5rem;">
        Rejoignez Mnémo et commencez à mémoriser.
    </p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nom d'utilisateur --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nom d'utilisateur</label>
            <input id="name"
                   type="text"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name"
                   placeholder="Votre prénom ou pseudo">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Adresse e-mail --}}
        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input id="email"
                   type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   required
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
                   autocomplete="new-password"
                   placeholder="Au moins 8 caractères">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirmation du mot de passe --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input id="password_confirmation"
                   type="password"
                   name="password_confirmation"
                   class="form-control"
                   required
                   autocomplete="new-password"
                   placeholder="Répétez le mot de passe">
        </div>

        {{-- Bouton d'inscription --}}
        <button type="submit" class="btn btn-primary w-100 mb-3">
            Créer mon compte
        </button>

        {{-- Liens secondaires --}}
        <div class="d-flex justify-content-between" style="font-size:.82rem;">
            <a href="{{ route('login') }}">Déjà inscrit ?</a>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color:var(--text-muted);">Mot de passe oublié ?</a>
            @endif
        </div>

    </form>

</x-guest-layout>
