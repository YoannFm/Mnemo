<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examen terminé</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #0d1117;
            color: #e6edf3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .guest-card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 480px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="guest-card">
        <i class="bi bi-check-circle-fill" style="font-size:3.5rem;color:#22c55e;"></i>
        <h4 class="mt-3 mb-2">Examen terminé !</h4>
        <p style="color:#8b949e;font-size:.95rem;margin-bottom:1.5rem;">
            Vos résultats ont été transmis à
            <strong style="color:#e6edf3;">{{ $sharedExam->user->name }}</strong>.
        </p>
        <div style="background:#0d1117;border:1px solid #30363d;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem;">
            <p style="color:#8b949e;font-size:.85rem;margin:0;">
                <i class="bi bi-info-circle me-1"></i>
                Merci d'avoir participé. Les résultats détaillés seront consultés par votre enseignant.
            </p>
        </div>
        <p style="color:#6e7681;font-size:.8rem;margin:0;">Vous pouvez fermer cette page.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
