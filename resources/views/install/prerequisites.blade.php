<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des prérequis - Mnemo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --accent: #6366f1;
            --card-bg: #1a1a1a;
            --text-primary: #e5e7eb;
            --text-muted: #9ca3af;
            --card-border: #374151;
            --success: #10b981;
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

        .check-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            justify-content: space-between;
        }

        .check-item.passed {
            border-color: rgba(16, 185, 129, 0.3);
        }

        .check-item.failed {
            border-color: rgba(239, 68, 68, 0.3);
        }

        .check-name {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .check-icon {
            font-size: 1.5rem;
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .check-icon.passed {
            color: var(--success);
        }

        .check-icon.failed {
            color: var(--error);
        }

        .check-details {
            display: flex;
            flex-direction: column;
        }

        .check-label {
            font-weight: 500;
        }

        .check-value {
            font-size: 0.85rem;
            color: var(--text-muted);
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

        .btn-primary-custom:hover:not(:disabled) {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .btn-primary-custom:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
            width: 40%;
            transition: width 0.3s ease;
        }

        .status-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .status-message.show {
            display: block;
        }

        .status-message.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .status-message.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #86efac;
        }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>Vérification des prérequis</h1>
            <p>Assurez-vous que votre serveur respecte tous les critères</p>
        </div>

        <div class="progress-bar-install">
            <div></div>
        </div>

        @if (!$allPassed)
            <div class="status-message error show">
                <strong>⚠️ Erreur:</strong> Certains prérequis ne sont pas satisfaits. Veuillez contacter votre hébergeur.
            </div>
        @else
            <div class="status-message success show">
                <strong>✓ Succès:</strong> Tous les prérequis sont satisfaits !
            </div>
        @endif

        <div class="checks-list">
            @foreach ($checks as $key => $check)
                <div class="check-item {{ $check['passed'] ? 'passed' : 'failed' }}">
                    <div class="check-name">
                        <div class="check-icon {{ $check['passed'] ? 'passed' : 'failed' }}">
                            @if ($check['passed'])
                                <i class="bi bi-check-circle-fill"></i>
                            @else
                                <i class="bi bi-x-circle-fill"></i>
                            @endif
                        </div>
                        <div class="check-details">
                            <span class="check-label">{{ $check['name'] }}</span>
                            @if (isset($check['version']))
                                <span class="check-value">Version actuelle: {{ $check['version'] }}</span>
                            @endif
                        </div>
                    </div>
                    <span style="font-size: 0.9rem; {{ $check['passed'] ? 'color: var(--success)' : 'color: var(--error)' }}">
                        {{ $check['passed'] ? 'OK' : 'NOK' }}
                    </span>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem;">
            @if ($allPassed)
                <a href="{{ route('install.database') }}" class="btn-primary-custom">
                    Continuer vers la base de données →
                </a>
            @else
                <button class="btn-primary-custom" disabled>
                    Continuez quand tous les prérequis seront satisfaits
                </button>
            @endif
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <small style="color: var(--text-muted);">
                Étape 2/5 - Vérification des prérequis
            </small>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>
