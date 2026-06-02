<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Mnemo</title>
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

        .wizard-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .wizard-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .wizard-title {
            font-size: 1.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .wizard-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .wizard-content {
            margin: 2rem 0;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            color: var(--text-muted);
        }

        .feature-list i {
            color: var(--accent);
            margin-right: 1rem;
            font-size: 1.25rem;
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
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <div class="wizard-logo">📚 Mnemo</div>
            <h1 class="wizard-title">Bienvenue !</h1>
            <p class="wizard-subtitle">Configurez votre application de mémorisation</p>
        </div>

        <div class="wizard-content">
            <p style="margin-bottom: 1.5rem;">
                Mnemo est une application pour créer des modules d'apprentissage et vous entraîner avec des modes interactifs.
            </p>

            <h5 style="margin-bottom: 1rem; color: var(--accent);">Ce que vous pouvez faire :</h5>
            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill"></i> Créer des modules personnalisés</li>
                <li><i class="bi bi-check-circle-fill"></i> Générer automatiquement des QCM</li>
                <li><i class="bi bi-check-circle-fill"></i> Mode Anki pour la révision spaced-repetition</li>
                <li><i class="bi bi-check-circle-fill"></i> Suivre votre progression</li>
            </ul>

            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 1.5rem;">
                L'installation prendra environ 2 minutes. Vous allez vérifier les prérequis, configurer la base de données et créer votre compte administrateur.
            </p>
        </div>

        <a href="{{ route('install.prerequisites') }}" class="btn-primary-custom">
            Commencer l'installation →
        </a>

        <div style="text-align: center; margin-top: 2rem;">
            <small style="color: var(--text-muted);">
                Étape 1/5 - Bienvenue
            </small>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>
