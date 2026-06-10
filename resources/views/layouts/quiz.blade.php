<!DOCTYPE html>
<html lang="fr" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($pageTitle) ? $pageTitle . ' - Mnémo' : 'Mnémo' }}</title>
    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --accent: {{ auth()->check() && auth()->user()->accent_color ? auth()->user()->accent_color : setting('theme_accent', '#EFB702') }};
            --accent-hover: color-mix(in srgb, var(--accent) 85%, black);
            --accent-light: color-mix(in srgb, var(--accent) 15%, transparent);
            --text-primary: {{ setting('theme_text_color', '#e2e8f0') }};
            --text-muted: #9ca3af;
            --card-bg: {{ setting('theme_card_bg', '#212227') }};
            --card-border: {{ setting('theme_content_bg', '#2E2E34') }};
            --body-bg: {{ setting('theme_body_bg', '#111113') }};
            --success-color: #22c55e;
            --danger-color: #ff5956;
        }
        [data-bs-theme="light"] {
            --body-bg: {{ setting('theme_light_body_bg', '#f0f2f5') }};
            --card-bg: {{ setting('theme_light_card_bg', '#ffffff') }};
            --card-border: {{ setting('theme_light_content_bg', '#e9ecef') }};
            --text-primary: {{ setting('theme_light_text_color', '#212529') }};
            --text-muted: #6c757d;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Rubik', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        ::selection { background: var(--accent); color: #212227; }
        ::-webkit-scrollbar { width: 7px; } ::-webkit-scrollbar-track { background: var(--card-bg); } ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }
        h1,h2,h3,h4,h5,h6 { font-family: 'Rubik', sans-serif; font-weight: 700; }
        .card { background: var(--card-bg); border: 2px solid var(--accent); border-radius: 5px; color: var(--text-primary); }
        .btn { font-weight: 700; text-transform: uppercase; letter-spacing: .5px; border-radius: 0; }
        .btn-primary { background: var(--accent); border-color: var(--accent); color: #212227; }
        .btn-primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); color: #212227; }
        .form-control, .form-select { background: var(--card-bg) !important; border: 1px solid var(--card-border) !important; color: var(--text-primary) !important; border-radius: 0; }
        .form-control:focus, .form-select:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 3px var(--accent-light) !important; }
        .alert-success { background: rgba(34,197,94,.1); border-color: rgba(34,197,94,.3); color: var(--success-color); }
        .alert-danger { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.3); color: var(--danger-color); }
        .progress { background: var(--card-border); }
    </style>
    @stack('styles')
</head>
<body>
    <div style="max-width:680px;margin:0 auto;padding:1.5rem 1rem 2rem;">
        {{ $slot }}
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
