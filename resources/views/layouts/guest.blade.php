<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — Mnémo' : 'Mnémo' }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --body-bg: #13162b;
            --card-bg: #181c2a;
            --card-border: #252a3d;
            --text-primary: #f1f5f9;
            --text-muted: #8b9bb4;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--body-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
        }

        /* Carte de connexion / inscription */
        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 440px;
        }

        /* Logo texte */
        .brand-logo {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -.5px;
        }

        .brand-logo span {
            color: var(--text-primary);
        }

        /* Champs de formulaire */
        .form-control {
            background: #0f1117;
            border: 1px solid var(--card-border);
            color: var(--text-primary);
            border-radius: 8px;
            padding: .6rem .9rem;
        }

        .form-control:focus {
            background: #0f1117;
            border-color: var(--accent);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        }

        .form-control::placeholder { color: var(--text-muted); }

        .form-label {
            font-size: .875rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: .35rem;
        }

        /* Bouton principal */
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            font-weight: 600;
            padding: .6rem 1.5rem;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
        }

        /* Liens --*/
        a { color: var(--accent); }
        a:hover { color: var(--accent-hover); }

        /* Message d'erreur Bootstrap */
        .invalid-feedback { font-size: .8rem; }
    </style>
</head>
<body>
    <div class="auth-card">

        {{-- Logo centré --}}
        <div class="text-center mb-4">
            <a href="/" class="brand-logo text-decoration-none">
                Mn<span>émo</span>
            </a>
            <p style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">
                Application de mémorisation
            </p>
        </div>

        {{-- Contenu de la page (formulaire login/register/etc.) --}}
        {{ $slot }}

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
