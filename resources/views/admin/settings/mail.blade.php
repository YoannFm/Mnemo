<x-admin-layout>
    <x-slot name="pageTitle">Paramètres e-mail</x-slot>

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        La configuration e-mail est nécessaire pour l'envoi des mots de passe oubliés, des notifications et la vérification des comptes.
    </div>

    <div id="mailAlert"></div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuration e-mail</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.mail.update') }}" method="POST" id="mailForm">
                @csrf

                <div class="row gx-3">
                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="mailerSelect">Méthode d'envoi</label>
                        <select class="form-select @error('mailer') is-invalid @enderror"
                                id="mailerSelect" name="mailer">
                            <option value="array" {{ $currentMailer === 'array' ? 'selected' : '' }}>
                                Désactivé
                            </option>
                            <option value="smtp" {{ $currentMailer === 'smtp' ? 'selected' : '' }}>
                                SMTP
                            </option>
                            <option value="sendmail" {{ $currentMailer === 'sendmail' ? 'selected' : '' }}>
                                Sendmail
                            </option>
                        </select>
                        @error('mailer')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3 col-md-8">
                        <label class="form-label" for="fromAddressInput">Adresse expéditeur</label>
                        <input type="email"
                               class="form-control @error('from_address') is-invalid @enderror"
                               id="fromAddressInput" name="from_address"
                               value="{{ old('from_address', $smtpConfig['from']) }}" required>
                        @error('from_address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Section SMTP --}}
                <div id="smtpSection" style="display:none;">
                    <hr>
                    <h6 class="mb-3">Configuration SMTP</h6>

                    <div class="row gx-3">
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="smtpHostInput">Hôte SMTP</label>
                            <input type="text"
                                   class="form-control @error('smtp_host') is-invalid @enderror"
                                   id="smtpHostInput" name="smtp_host"
                                   value="{{ old('smtp_host', $smtpConfig['host']) }}"
                                   placeholder="smtp.gmail.com">
                            @error('smtp_host')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3 col-md-3">
                            <label class="form-label" for="smtpPortInput">Port</label>
                            <input type="number" min="1" max="65535"
                                   class="form-control @error('smtp_port') is-invalid @enderror"
                                   id="smtpPortInput" name="smtp_port"
                                   value="{{ old('smtp_port', $smtpConfig['port']) }}">
                            @error('smtp_port')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3 col-md-3">
                            <label class="form-label" for="smtpSchemeSelect">Chiffrement</label>
                            <select class="form-select" id="smtpSchemeSelect" name="smtp_scheme">
                                <option value="" {{ !($smtpConfig['scheme'] ?? null) ? 'selected' : '' }}>Auto</option>
                                <option value="smtp" {{ ($smtpConfig['scheme'] ?? '') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="smtps" {{ ($smtpConfig['scheme'] ?? '') === 'smtps' ? 'selected' : '' }}>SMTPS</option>
                            </select>
                        </div>
                    </div>

                    <div class="row gx-3">
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="smtpUsernameInput">Nom d'utilisateur</label>
                            <input type="text"
                                   class="form-control @error('smtp_username') is-invalid @enderror"
                                   id="smtpUsernameInput" name="smtp_username"
                                   value="{{ old('smtp_username', $smtpConfig['username']) }}">
                            @error('smtp_username')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="smtpPasswordInput">Mot de passe</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('smtp_password') is-invalid @enderror"
                                       id="smtpPasswordInput" name="smtp_password"
                                       placeholder="Laisser vide pour ne pas modifier">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('smtp_password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                {{-- Section Sendmail --}}
                <div id="sendmailSection" style="display:none;">
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Sendmail utilise le serveur de mail local du serveur. Assurez-vous qu'il est correctement configuré sur votre hôte.
                    </div>
                </div>

                <hr>

                {{-- Vérification email si mailer actif --}}
                <div id="verificationSection" style="display:none;">
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="verificationSwitch"
                               name="users_email_verification"
                               {{ \App\Models\Setting::get('mail.users_email_verification') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="verificationSwitch">
                            Vérification email des utilisateurs
                        </label>
                    </div>
                </div>

                <div id="disabledMailAlert" style="display:none;">
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Les emails sont désactivés. Activez un mailer pour utiliser les fonctionnalités e-mail.
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Enregistrer
                    </button>
                    <button type="button" class="btn btn-success" id="sendTestMail" style="display:none;">
                        <i class="bi bi-send me-1"></i> Envoyer un e-mail de test
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const mailerSelect = document.getElementById('mailerSelect');
        const smtpSection = document.getElementById('smtpSection');
        const sendmailSection = document.getElementById('sendmailSection');
        const verificationSection = document.getElementById('verificationSection');
        const disabledMailAlert = document.getElementById('disabledMailAlert');
        const sendTestMailBtn = document.getElementById('sendTestMail');

        function updateMailerUI() {
            const val = mailerSelect.value;
            smtpSection.style.display = val === 'smtp' ? '' : 'none';
            sendmailSection.style.display = val === 'sendmail' ? '' : 'none';
            verificationSection.style.display = (val !== 'array') ? '' : 'none';
            disabledMailAlert.style.display = (val === 'array') ? '' : 'none';
            sendTestMailBtn.style.display = (val !== 'array') ? '' : 'none';
        }

        mailerSelect.addEventListener('change', updateMailerUI);
        updateMailerUI();

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('smtpPasswordInput');
            const icon = document.getElementById('togglePasswordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });

        // Send test mail
        document.getElementById('sendTestMail').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Envoi...';

            fetch('{{ route('admin.settings.mail.send') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                const alertDiv = document.getElementById('mailAlert');
                const cls = ok ? 'alert-success' : 'alert-danger';
                const icon = ok ? 'check-circle' : 'exclamation-triangle';
                alertDiv.innerHTML = `<div class="alert ${cls} alert-dismissible"><i class="bi bi-${icon} me-1"></i>${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
            })
            .catch(() => {
                document.getElementById('mailAlert').innerHTML = '<div class="alert alert-danger">Une erreur est survenue.</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-1"></i> Envoyer un e-mail de test';
            });
        });
    </script>
</x-admin-layout>
