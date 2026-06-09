<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examen partagé — {{ $sharedExam->module->title }}</title>
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
        }
        .form-control, .form-select {
            background: #0d1117;
            border-color: #30363d;
            color: #e6edf3;
        }
        .form-control:focus, .form-select:focus {
            background: #0d1117;
            border-color: #58a6ff;
            color: #e6edf3;
            box-shadow: 0 0 0 .2rem rgba(88,166,255,.15);
        }
        .form-control::placeholder {
            color: #6e7681;
        }
        .btn-primary {
            background: #238636;
            border-color: #238636;
        }
        .btn-primary:hover {
            background: #2ea043;
            border-color: #2ea043;
        }
    </style>
</head>
<body>
    <div class="guest-card">

        @if (session('expired') || $sharedExam->isExpired())
            <div class="text-center py-3">
                <i class="bi bi-clock-history" style="font-size:3rem;color:#ef4444;"></i>
                <h5 class="mt-3">Lien expiré</h5>
                <p style="color:#8b949e;font-size:.9rem;">
                    Ce lien d'examen a expiré. Contactez votre enseignant pour en obtenir un nouveau.
                </p>
            </div>
        @else
            <div class="text-center mb-4">
                <i class="bi bi-pencil-square" style="font-size:2.5rem;color:#58a6ff;"></i>
                <h5 class="mt-3 mb-1">Examen partagé</h5>
                <p style="color:#8b949e;font-size:.9rem;margin-bottom:.5rem;">
                    <strong style="color:#e6edf3;">{{ $sharedExam->user->name }}</strong> vous a invité à passer un examen
                </p>
                <div style="background:#0d1117;border:1px solid #30363d;border-radius:8px;padding:.6rem 1rem;display:inline-block;">
                    <span style="font-weight:600;font-size:1rem;">{{ $sharedExam->module->title }}</span>
                </div>
                @if ($sharedExam->label)
                    <div style="margin-top:.75rem;color:#8b949e;font-size:.85rem;">
                        <i class="bi bi-tag me-1"></i>{{ $sharedExam->label }}
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('guest.exam.start', $sharedExam->uuid) }}">
                @csrf
                <div class="mb-4">
                    <label for="guest_name" class="form-label" style="font-size:.875rem;color:#8b949e;">
                        Votre prénom / nom
                    </label>
                    <input type="text"
                           class="form-control @error('guest_name') is-invalid @enderror"
                           id="guest_name"
                           name="guest_name"
                           placeholder="Ex: Marie Dupont"
                           maxlength="100"
                           value="{{ old('guest_name') }}"
                           required
                           autofocus>
                    @error('guest_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-play-fill me-1"></i>Commencer l'examen
                </button>
            </form>
        @endif

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
