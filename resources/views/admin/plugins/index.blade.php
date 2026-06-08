<x-admin-layout>
    <x-slot name="pageTitle">Plugins</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Plugins</h4>
        <form action="{{ route('admin.plugins.reload') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Actualiser
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($updates->isNotEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-arrow-up-circle-fill"></i>
            <strong>{{ $updates->count() }} mise(s) à jour disponible(s)</strong>
        </div>
    @endif

    <h5 class="mb-3">Installés ({{ $installed->count() }})</h5>

    @if($installed->isEmpty())
        <div class="card mb-4">
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-puzzle" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucun plugin installé.</p>
            </div>
        </div>
    @else
        <div class="row g-3 mb-5">
            @foreach($installed as $id => $plugin)
                @php $hasUpdate = $updates->has($id); @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 {{ $hasUpdate ? 'border-warning' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $plugin->name }}</h6>
                                    <small class="text-muted">v{{ $plugin->version ?? '?' }} — {{ $plugin->author ?? '' }}</small>
                                </div>
                                @if($plugin->is_enabled)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </div>
                            @if(!empty($plugin->description))
                                <p class="text-muted small mb-3">{{ $plugin->description }}</p>
                            @endif
                            <div class="d-flex gap-1 flex-wrap">
                                @if($plugin->is_enabled)
                                    <form action="{{ route('admin.plugins.disable', $id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-pause-circle"></i> Désactiver</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.plugins.enable', $id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-sm"><i class="bi bi-play-circle"></i> Activer</button>
                                    </form>
                                @endif
                                @if($hasUpdate)
                                    <form action="{{ route('admin.plugins.update', $id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-warning btn-sm"><i class="bi bi-arrow-up-circle"></i> Mettre à jour</button>
                                    </form>
                                @endif
                                @if(!$plugin->is_enabled)
                                    <form action="{{ route('admin.plugins.delete', $id) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h5 class="mb-3">Disponibles sur MnemoCloud ({{ $available->count() }})</h5>

    @if($available->isEmpty())
        <div class="card">
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-cloud-slash" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">
                    @if(!setting('site_key') || !setting('mnemocloud_url'))
                        Configurez la clé et l'URL MnemoCloud dans les <a href="{{ route('admin.settings.index') }}">paramètres</a>.
                    @else
                        Aucun plugin disponible ou MnemoCloud inaccessible.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($available as $plugin)
                @php
                    $slug = $plugin['slug'];
                    $alreadyInstalled = $installed->has($slug);
                    $hasLicense = $plugin['has_license'] ?? false;
                    $latest = $plugin['latest_version'] ?? null;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $plugin['name'] }}</h6>
                                    <small class="text-muted">{{ $plugin['author'] ?? '' }}@if($latest) · v{{ $latest['version'] }}@endif</small>
                                </div>
                                @if($plugin['is_free'])
                                    <span class="badge bg-success">Gratuit</span>
                                @else
                                    <span class="badge bg-primary">Payant</span>
                                @endif
                            </div>
                            @if(!empty($plugin['description']))
                                <p class="text-muted small mb-3">{{ $plugin['description'] }}</p>
                            @endif
                            @if($alreadyInstalled)
                                <span class="badge bg-secondary"><i class="bi bi-check me-1"></i>Déjà installé</span>
                            @elseif($hasLicense && $latest)
                                <form action="{{ route('admin.plugins.install', $slug) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-download me-1"></i> Installer</button>
                                </form>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-lock me-1"></i>Licence requise</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>
