<x-guest-layout>
    <div class="mb-4" style="font-size:.875rem;color:var(--text-muted);">
        Veuillez saisir le code à 6 chiffres généré par votre application d'authentification, ou l'un de vos codes de récupération.
    </div>

    <form method="POST" action="{{ route('two-factor.store') }}">
        @csrf

        <div class="mb-4">
            <label for="code" class="form-label">Code d'authentification</label>
            <input type="text"
                   id="code"
                   name="code"
                   class="form-control @error('code') is-invalid @enderror"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   autofocus
                   placeholder="000000">
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-shield-check me-2"></i>Vérifier
            </button>
        </div>
    </form>
</x-guest-layout>
