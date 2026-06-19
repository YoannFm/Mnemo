<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f4f4f5; margin:0; padding:2rem 0; }
        .container { max-width:560px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#1e1e2e; padding:1.5rem 2rem; }
        .header h1 { color:#EFB702; margin:0; font-size:1.2rem; font-weight:700; letter-spacing:.5px; }
        .body { padding:2rem; }
        .body h2 { font-size:1.1rem; color:#111; margin:0 0 1rem; }
        .body p { color:#444; font-size:.95rem; line-height:1.6; margin:0 0 1.5rem; }
        .btn { display:inline-block; background:#EFB702; color:#111; padding:.6rem 1.4rem; border-radius:8px; text-decoration:none; font-weight:600; font-size:.9rem; }
        .body ul, .body ol { color:#444; font-size:.95rem; line-height:1.6; margin:0 0 1rem; padding-left:1.5rem; }
        .body li { margin-bottom:.3rem; }
        .body strong { color:#111; }
        .footer { padding:1rem 2rem; background:#f9f9f9; border-top:1px solid #eee; font-size:.78rem; color:#999; text-align:center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="body">
            <h2>{{ $notification->title }}</h2>
            <p>{!! $notification->message !!}</p>
            <a href="{{ config('app.url') }}" class="btn">Accéder au site</a>
        </div>
        <div class="footer">
            Vous recevez cet e-mail car les notifications par mail sont activées sur votre compte.<br>
            Pour ne plus recevoir ces e-mails, désactivez-les dans vos
            <a href="{{ config('app.url') }}/profile#email-notifications" style="color:#EFB702;">paramètres de profil</a>.
        </div>
    </div>
</body>
</html>
