<x-admin-layout>
    <x-slot name="pageTitle">Paramètres e-mail</x-slot>

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle"></i>
        La configuration e-mail est nécessaire pour l'envoi des mots de passe oubliés et des notifications.
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configuration e-mail</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.mail.update') }}" method="POST">
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
                                @error('mailer')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-8">
                                <label class="form-label" for="fromAddressInput">Adresse expéditeur</label>
                                <input type="email"
                                       class="form-control @error('from_address') is-invalid @enderror"
                                       id="fromAddressInput" name="from_address"
                                       value="{{ old('from_address', $smtpConfig['from']) }}" required>
                                @error('from_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Configuration SMTP</h6>

                        <div class="row gx-3">
                            <div class="mb-3 col-md-8">
                                <label class="form-label" for="smtpHostInput">Hôte SMTP</label>
                                <input type="text"
                                       class="form-control @error('smtp_host') is-invalid @enderror"
                                       id="smtpHostInput" name="smtp_host"
                                       value="{{ old('smtp_host', $smtpConfig['host']) }}"
                                       placeholder="smtp.gmail.com">
                                @error('smtp_host')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label" for="smtpPortInput">Port</label>
                                <input type="number" min="1" max="65535"
                                       class="form-control @error('smtp_port') is-invalid @enderror"
                                       id="smtpPortInput" name="smtp_port"
                                       value="{{ old('smtp_port', $smtpConfig['port']) }}">
                                @error('smtp_port')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row gx-3">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="smtpUsernameInput">Nom d'utilisateur</label>
                                <input type="text"
                                       class="form-control @error('smtp_username') is-invalid @enderror"
                                       id="smtpUsernameInput" name="smtp_username"
                                       value="{{ old('smtp_username', $smtpConfig['username']) }}">
                                @error('smtp_username')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="smtpPasswordInput">Mot de passe</label>
                                <input type="password"
                                       class="form-control @error('smtp_password') is-invalid @enderror"
                                       id="smtpPasswordInput" name="smtp_password"
                                       placeholder="Laisser vide pour ne pas modifier">
                                @error('smtp_password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statut actuel</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Méthode :</strong>
                        <span class="badge {{ $currentMailer !== 'array' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $currentMailer === 'array' ? 'Désactivé' : strtoupper($currentMailer) }}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Hôte :</strong> {{ $smtpConfig['host'] ?: '—' }}</p>
                    <p class="mb-1"><strong>Port :</strong> {{ $smtpConfig['port'] }}</p>
                    <p class="mb-0"><strong>Expéditeur :</strong> {{ $smtpConfig['from'] ?: '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
