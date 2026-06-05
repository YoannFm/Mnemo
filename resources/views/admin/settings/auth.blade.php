<x-admin-layout>
    <x-slot name="pageTitle">Paramètres - Authentification</x-slot>

    {{-- Card Authentification --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Authentification</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.auth.update') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="registration_conditions">Conditions d'inscription</label>
                    <textarea id="registration_conditions" name="registration_conditions"
                              class="form-control @error('registration_conditions') is-invalid @enderror"
                              rows="4">{{ old('registration_conditions', $settings['registration_conditions']) }}</textarea>
                    @error('registration_conditions')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="registration_enabled"
                               name="registration_enabled" value="1"
                               {{ old('registration_enabled', $settings['registration_enabled']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="registration_enabled">
                            Activer l'inscription des utilisateurs
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_name_change"
                               name="allow_name_change" value="1"
                               {{ old('allow_name_change', $settings['allow_name_change']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_name_change">
                            Permettre aux utilisateurs de changer leur nom
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_account_deletion"
                               name="allow_account_deletion" value="1"
                               {{ old('allow_account_deletion', $settings['allow_account_deletion']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_account_deletion">
                            Permettre aux utilisateurs de supprimer leur compte
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>

    {{-- Card Sécurité --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Sécurité</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.auth.update') }}">
                @csrf

                {{-- Preserve auth fields as hidden so they're not wiped on security-only save --}}
                <input type="hidden" name="registration_conditions" value="{{ $settings['registration_conditions'] }}">
                <input type="hidden" name="registration_enabled" value="{{ $settings['registration_enabled'] }}">
                <input type="hidden" name="allow_name_change" value="{{ $settings['allow_name_change'] }}">
                <input type="hidden" name="allow_account_deletion" value="{{ $settings['allow_account_deletion'] }}">

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="email_verification_required"
                               name="email_verification_required" value="1"
                               {{ old('email_verification_required', $settings['email_verification_required']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_verification_required">
                            Forcer la vérification d'email
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="admin_2fa_required"
                               name="admin_2fa_required" value="1"
                               {{ old('admin_2fa_required', $settings['admin_2fa_required']) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="admin_2fa_required">
                            2FA obligatoire pour les administrateurs
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>

<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
(function () {
    function initTinyMCE() {
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        if (tinymce.get('registration_conditions')) tinymce.remove('#registration_conditions');
        tinymce.init({
            selector: '#registration_conditions',
            base_url: '{{ asset('vendor/tinymce') }}',
            license_key: 'gpl',
            promotion: false,
            height: 300,
            plugins: 'searchreplace autolink code link lists',
            toolbar: 'blocks bold italic underline strikethrough | link | alignleft aligncenter alignright | bullist numlist | removeformat code | undo redo',
            skin: dark ? 'oxide-dark' : 'oxide',
            content_css: dark ? 'dark' : 'default',
        });
    }
    initTinyMCE();
    new MutationObserver(function (m) { m.forEach(function (mm) { if (mm.attributeName === 'data-bs-theme') initTinyMCE(); }); }).observe(document.documentElement, { attributes: true });
})();
</script>
</x-admin-layout>
