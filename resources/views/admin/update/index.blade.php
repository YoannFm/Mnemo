<x-admin-layout>
    <x-slot name="pageTitle">Mises à jour</x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

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

            @if ($hasUpdate)
                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Une nouvelle version est disponible : <strong>v{{ $latest }}</strong>.
                </div>

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
                          onsubmit="return confirm('Installer la mise à jour v{{ $latest }} ? L\'application sera brièvement indisponible.')">
                        @csrf
                        <button class="btn btn-success">
                            <i class="bi bi-arrow-up-circle me-1"></i>Installer v{{ $latest }}
                        </button>
                    </form>
                @endif
            @elseif ($latest)
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    Mnémo est à jour (v{{ $currentVersion }}).
                </div>
            @else
                <div class="text-muted small">
                    <i class="bi bi-wifi-off me-1"></i>Impossible de contacter le serveur de mises à jour. Cliquez sur "Vérifier" pour réessayer.
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
