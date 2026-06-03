<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance - {{ \App\Models\Setting::get('site_name', 'Mnémo') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #111113;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .maintenance-card {
            background: #212227;
            border: 2px solid #EFB702;
            border-radius: 8px;
            padding: 3rem 2.5rem;
            max-width: 600px;
            text-align: center;
        }
        .maintenance-icon {
            font-size: 4rem;
            color: #EFB702;
        }
        h1 { color: #EFB702; font-weight: 800; }
    </style>
</head>
<body>
    <div class="maintenance-card shadow">
        <div class="maintenance-icon mb-3">
            <i class="bi bi-tools"></i>
        </div>
        <h1 class="mb-3">Maintenance</h1>
        <div>
            {!! \App\Models\Setting::get('maintenance_message', 'Le site est actuellement en maintenance. Merci de revenir plus tard.') !!}
        </div>
    </div>
</body>
</html>
