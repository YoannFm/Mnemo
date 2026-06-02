<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte admin - Mnemo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --accent: #6366f1;
            --card-bg: #1a1a1a;
            --text-primary: #e5e7eb;
            --text-muted: #9ca3af;
            --card-border: #374151;
            --error: #ef4444;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .wizard-container {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .wizard-header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .wizard-header p {
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(99, 102, 241, 0.1);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        .is-invalid {
            border-color: var(--error) !important;
        }

        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .btn-primary-custom {
            background: var(--accent);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .progress-bar-install {
            height: 4px;
            background: var(--card-border);
            border-radius: 2px;
            margin: 2rem 0;
            overflow: hidden;
        }

        .progress-bar-install > div {
            height: 100%;
            background: var(--accent);
            width: 80%;
            transition: width 0.3s ease;
        }

        .password-requirements {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #c7d2fe;
        }

        .req-item {
            display: flex;
            align-items: center;
            padding: 0.25rem 0;
        }

        .req-item i {
            margin-right: 0.5rem;
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>Créer un compte admin</h1>
            <p>Cet utilisateur sera l'administrateur de votre instance Mnemo</p>
        </div>

        <div class="progress-bar-install">
            <div></div>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; color: #fca5a5;">
                <strong>⚠️ Erreur:</strong>
                <ul style="margin-bottom: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('install.admin.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="Jean Dupont" class="@error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="admin@mnemo.local" class="@error('email') is-invalid @enderror" required>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" class="@error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                <div class="password-requirements">
                    <div class="req-item">
                        <i class="bi bi-info-circle"></i>
                        Minimum 8 caractères
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="••••••••" class="@error('password_confirmation') is-invalid @enderror" required>
                @error('password_confirmation')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary-custom">
                Créer le compte admin →
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem;">
            <small style="color: var(--text-muted);">
                Étape 4/5 - Création du compte admin
            </small>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');

        function checkMatch() {
            if (passwordInput.value && confirmInput.value) {
                if (passwordInput.value !== confirmInput.value) {
                    confirmInput.classList.add('is-invalid');
                } else {
                    confirmInput.classList.remove('is-invalid');
                }
            }
        }

        confirmInput.addEventListener('input', checkMatch);
        passwordInput.addEventListener('input', checkMatch);
    </script>
</body>
</html>
