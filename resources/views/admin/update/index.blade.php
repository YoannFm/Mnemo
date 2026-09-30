<x-admin-layout>
    <x-slot name="pageTitle">Mises à jour</x-slot>

    {{-- Versions --}}
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-arrow-up-circle me-2"></i>Mise à jour de l'application</h5>
            <form method="POST" action="{{ route('admin.update.fetch') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Vérifier
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="p-3 border rounded text-center">
                        <div class="text-muted small mb-1">Version actuelle</div>
                        <div class="fw-semibold fs-5">v{{ $currentVersion }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 border rounded text-center">
                        <div class="text-muted small mb-1">Dernière version</div>
                        <div class="fw-semibold fs-5">
                            @if ($latest)
                                v{{ $latest }}
                                @if ($hasUpdate)
                                    <span class="badge bg-warning text-dark ms-1">Disponible</span>
                                @else
                                    <span class="badge bg-success ms-1">À jour</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (!$latest)
                <div class="text-muted small">
                    <i class="bi bi-wifi-off me-1"></i>Aucune version publiée sur GitHub, ou GitHub injoignable. Cliquez sur "Vérifier" pour réessayer.
                </div>
            @elseif (!$hasUpdate)
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    Mnémo est à jour (v{{ $currentVersion }}).
                </div>
            @endif
        </div>
    </div>

    {{-- Sécurité avant mise à jour --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Sauvegardes avant mise à jour</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Téléchargez une sauvegarde complète du site à tout moment. Indispensable avant une mise à jour.</p>

            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="p-3 border rounded text-center" id="backup-files-card">
                        <i class="bi bi-folder-fill fs-3 text-primary mb-2 d-block"></i>
                        <div class="fw-semibold mb-1">Archive des fichiers</div>
                        <div class="text-muted small mb-3">ZIP de tous les fichiers du site (hors vendor et node_modules)</div>
                        <a href="{{ route('admin.update.backup-files') }}"
                           class="btn btn-outline-primary btn-sm"
                           id="btn-backup-files"
                           onclick="markDone('files')">
                            <i class="bi bi-download me-1"></i>Télécharger l'archive
                        </a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 border rounded text-center" id="backup-db-card">
                        <i class="bi bi-database-fill fs-3 text-warning mb-2 d-block"></i>
                        <div class="fw-semibold mb-1">Export base de données</div>
                        <div class="text-muted small mb-3">Fichier SQL contenant toutes les données du site</div>
                        <a href="{{ route('admin.update.backup-database') }}"
                           class="btn btn-outline-warning btn-sm"
                           id="btn-backup-db"
                           onclick="markDone('db')">
                            <i class="bi bi-download me-1"></i>Télécharger le SQL
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if ($hasUpdate)
    {{-- Installation --}}
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-cloud-download me-2"></i>Installation de v{{ $latest }}</h5>
        </div>
        <div class="card-body">
            @if (!$isDownloaded)
                <form method="POST" action="{{ route('admin.update.download') }}">
                    @csrf
                    <button class="btn btn-primary">
                        <i class="bi bi-cloud-download me-1"></i>Télécharger la mise à jour
                    </button>
                </form>
            @else
                <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    Mise à jour téléchargée et prête à installer.
                </div>
                <form method="POST" action="{{ route('admin.update.install') }}"
                      onsubmit="return confirmInstall(event)">
                    @csrf
                    <button class="btn btn-success" id="btn-install" disabled>
                        <i class="bi bi-arrow-up-circle me-1"></i>Installer v{{ $latest }}
                    </button>
                    <div class="text-muted small mt-2" id="install-hint">
                        <i class="bi bi-lock me-1"></i>Effectuez les deux sauvegardes pour déverrouiller l'installation.
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
    var backupsDone = { files: false, db: false };

    function markDone(type) {
        setTimeout(function() {
            backupsDone[type] = true;
            var card = document.getElementById('backup-' + type + '-card');
            if (card) {
                card.style.borderColor = '#198754';
                card.querySelector('i.fs-3').classList.add('text-success');
                card.querySelector('i.fs-3').classList.remove('text-primary', 'text-warning');
            }
            checkBackups();
        }, 500);
    }

    function checkBackups() {
        if (backupsDone.files && backupsDone.db) {
            var btn = document.getElementById('btn-install');
            var hint = document.getElementById('install-hint');
            var warning = document.getElementById('backup-warning');
            if (btn) btn.disabled = false;
            if (hint) hint.innerHTML = '';
            if (warning) {
                warning.classList.remove('alert-warning');
                warning.classList.add('alert-success');
                warning.innerHTML = '<i class="bi bi-check-circle-fill"></i> Sauvegardes effectuées. Vous pouvez installer la mise à jour.';
            }
        }
    }

    function confirmInstall(e) {
        if (!backupsDone.files || !backupsDone.db) {
            e.preventDefault();
            return false;
        }
        return confirm('Installer la mise à jour v{{ $latest }} ? L\'application sera brièvement indisponible.');
    }
    </script>
    @endif

</x-admin-layout>
