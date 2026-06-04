<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - Mnémo' : 'Mnémo' }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: {{ setting('theme_accent', '#EFB702') }};
            --accent-hover: color-mix(in srgb, {{ setting('theme_accent', '#EFB702') }} 85%, black);
            --accent-light: color-mix(in srgb, {{ setting('theme_accent', '#EFB702') }} 15%, transparent);
            --body-bg: {{ setting('theme_content_bg', '#2E2E34') }};
            --card-bg: {{ setting('theme_card_bg', '#212227') }};
            --card-border: {{ setting('theme_content_bg', '#2E2E34') }};
            --text-primary: {{ setting('theme_text_color', '#e2e8f0') }};
            --text-muted: #9ca3af;
        }

        body {
            font-family: 'Rubik', sans-serif;
            background: var(--body-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        ::selection { background: var(--accent); color: #212227; }
        ::-webkit-scrollbar { width: 7px; }
        ::-webkit-scrollbar-track { background: var(--card-bg); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        .auth-card {
            background: var(--card-bg);
            border: 2px solid var(--accent);
            border-radius: 5px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 440px;
        }

        .brand-logo {
            font-family: 'Rubik', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: -.5px;
        }

        .brand-logo span { color: var(--text-primary); }

        .form-control {
            background: var(--body-bg) !important;
            border: 2px solid var(--accent) !important;
            color: var(--text-primary) !important;
            border-radius: 0;
            padding: .6rem .9rem;
        }

        .form-control:focus {
            background: var(--body-bg) !important;
            border-color: var(--accent) !important;
            color: var(--text-primary) !important;
            box-shadow: 0 0 0 3px var(--accent-light) !important;
        }

        .form-control::placeholder { color: var(--text-muted); }

        .form-label {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: .35rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #212227;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: .6rem 1.5rem;
            border-radius: 0;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            color: #212227;
        }

        a { color: var(--accent); }
        a:hover { color: var(--accent-hover); }

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
