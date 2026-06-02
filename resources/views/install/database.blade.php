<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration de la base de données - Mnemo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --accent: #6366f1;
            --card-bg: #1a1a1a;
            --text-primary: #e5e7eb;
            --text-muted: #9ca3af;
            --card-border: #374151;
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

        .info-box {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #c7d2fe;
            font-size: 0.9rem;
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
            width: 60%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>Base de données</h1>
            <p>Configuration et initialisation</p>
        </div>

        <div class="progress-bar-install">
            <div></div>
        </div>

        <div class="info-box">
            <strong>ℹ️ SQLite est déjà utilisé</strong><br>
            Nous utilisons SQLite pour la simplicité. Les migrations vont être exécutées automatiquement pour créer les tables.
        </div>

        <p style="margin-bottom: 1.5rem; color: var(--text-muted);">
            Les migrations Laravel vont créer automatiquement toutes les tables nécessaires (utilisateurs, modules, items, progression, etc.).
        </p>

        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">
                <strong>Statut:</strong> Prêt à initialiser<br>
                <strong>Type:</strong> SQLite<br>
                <strong>Fichier:</strong> <code style="color: var(--accent);">database/database.sqlite</code>
            </p>
        </div>

        <a href="{{ route('install.admin') }}" class="btn-primary-custom">
            Continuer vers la création du compte admin →
        </a>

        <div style="text-align: center; margin-top: 2rem;">
            <small style="color: var(--text-muted);">
                Étape 3/5 - Configuration de la base de données
            </small>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>
