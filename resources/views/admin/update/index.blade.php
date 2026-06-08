<x-admin-layout>
    <x-slot name="pageTitle">Mises à jour</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Mises à jour de Mnémo</h4>
        <form action="{{ route('admin.update.fetch') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Vérifier
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-info-circle" style="color:var(--accent);"></i>
            <span class="fw-semibold">Version actuelle</span>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-secondary fs-6">v{{ $currentVersion }}</span>
                @if($hasUpdate)
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-arrow-up-circle me-1"></i>v{{ $latest['version'] }} disponible
                    </span>
                @else
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>À jour
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($hasUpdate && $latest)
        <div class="card border-warning">
            <div class="card-header d-flex align-items-center gap-2" style="border-color:rgba(255,193,7,.3);">
                <i class="bi bi-arrow-up-circle-fill text-warning"></i>
                <span class="fw-semibold">Mise à jour disponible — v{{ $latest['version'] }}</span>
                @if(!empty($latest['is_forced']))
                    <span class="badge bg-danger ms-2">Obligatoire</span>
                @endif
            </div>
            <div class="card-body">
                @if(!empty($latest['changelog']))
                    <h6 class="fw-semibold mb-2">Changelog</h6>
                    <div class="p-3 mb-4" style="background:var(--body-bg);border:1px solid var(--card-border);border-radius:4px;font-size:.85rem;white-space:pre-wrap;">{{ $latest['changelog'] }}</div>
                @endif

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if(!$isDownloaded)
                        <form action="{{ route('admin.update.download') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-download me-1"></i> Télécharger v{{ $latest['version'] }}
                            </button>
                        </form>
                    @else
                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Téléchargée</span>
                        <form action="{{ route('admin.update.install') }}" method="POST"
                              onsubmit="return confirm('Installer la mise à jour ? Le site sera brièvement indisponible.')">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-rocket-takeoff me-1"></i> Installer maintenant
                            </button>
                        </form>
                    @endif

                    @if(!empty($latest['released_at']))
                        <span class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ \Carbon\Carbon::parse($latest['released_at'])->format('d/m/Y') }}
                        </span>
                    @endif
                    @if(!empty($latest['file_size']))
                        <span class="text-muted small">
                            <i class="bi bi-file-zip me-1"></i>
                            {{ number_format($latest['file_size'] / 1024 / 1024, 1) }} Mo
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-admin-layout>
