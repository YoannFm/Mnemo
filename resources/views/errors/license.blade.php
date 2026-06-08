<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Licence invalide – Mnémo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background:#111113; color:#e2e8f0; font-family:'Segoe UI',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .box { max-width:480px; text-align:center; padding:2rem; }
        .icon { font-size:4rem; color:#ef4444; margin-bottom:1.5rem; }
        h1 { font-size:1.5rem; font-weight:700; margin-bottom:.75rem; }
        p { color:#9ca3af; font-size:.9rem; margin-bottom:1.5rem; }
        .reason { display:inline-block; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3); color:#ef4444; font-size:.78rem; padding:.3rem .8rem; border-radius:4px; font-family:monospace; margin-bottom:1.5rem; }
        a { color:#EFB702; text-decoration:none; font-size:.85rem; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon"><i class="bi bi-shield-x"></i></div>
        <h1>Licence invalide</h1>
        <p>Ce site ne dispose pas d'une licence Mnémo active.<br>Contactez l'administrateur pour régulariser la situation.</p>

        @php $reason = $data['reason'] ?? null; @endphp
        @if($reason)
            <div class="reason">{{ match($reason) {
                'not_configured' => 'Clé de site non configurée',
                'not_found'      => 'Clé de site introuvable',
                'inactive'       => 'Licence désactivée',
                'expired'        => 'Licence expirée',
                'domain_mismatch'=> 'Domaine non autorisé',
                'unreachable'    => 'Serveur de licence inaccessible',
                default          => $reason,
            } }}</div>
        @endif

        <br>
        <a href="mailto:contact@rascol.net"><i class="bi bi-envelope me-1"></i>Contacter le support</a>
    </div>
</body>
</html>
