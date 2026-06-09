<x-admin-layout>
    <x-slot name="pageTitle">Plugins</x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#installed">
                <i class="bi bi-puzzle-fill me-1"></i>Installés
                <span class="badge bg-secondary ms-1">{{ $installed->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#available">
                <i class="bi bi-cloud-download me-1"></i>Disponibles
                @if($available->count())
                    <span class="badge bg-primary ms-1">{{ $available->whereNull('is_installed')->count() + $available->where('is_installed', false)->count() }}</span>
                @endif
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- Installés --}}
        <div class="tab-pane fade show active" id="installed">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Plugins installés</h5>
                    <form method="POST" action="{{ route('admin.plugins.reload') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>Recharger
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @forelse ($installed as $plugin)
                        <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $plugin->name }}</div>
                                <div class="text-muted small">{{ $plugin->description ?? '' }}</div>
                                <div class="mt-1">
                                    <span class="badge bg-secondary">v{{ $plugin->version }}</span>
                                    @if ($plugin->is_enabled)
                                        <span class="badge bg-success">Activé</span>
                                    @else
                                        <span class="badge bg-danger">Désactivé</span>
                                    @endif
                                    @if (!empty($plugin->has_update))
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-arrow-up-circle me-1"></i>v{{ $plugin->latest_version }} disponible
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @if ($plugin->is_enabled)
                                    <form method="POST" action="{{ route('admin.plugins.disable', $plugin->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pause-fill me-1"></i>Désactiver
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.plugins.enable', $plugin->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-play-fill me-1"></i>Activer
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.plugins.update', $plugin->id) }}">
                                    @csrf
                                    <button class="btn btn-sm {{ !empty($plugin->has_update) ? 'btn-warning' : 'btn-outline-info' }}" title="Mettre à jour">
                                        <i class="bi bi-arrow-up-circle{{ !empty($plugin->has_update) ? '-fill' : '' }} me-1"></i>
                                        {{ !empty($plugin->has_update) ? 'Mettre à jour' : 'Réinstaller' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.plugins.delete', $plugin->id) }}"
                                      onsubmit="return confirm('Supprimer ce plugin ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-puzzle" style="font-size:3rem;"></i>
                            <p class="mt-3">Aucun plugin installé.<br>Allez dans l'onglet <strong>Disponibles</strong> pour en installer.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Disponibles depuis MnemoCloud --}}
        <div class="tab-pane fade" id="available">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-cloud me-2"></i>Plugins disponibles sur MnemoCloud</h5>
                </div>
                <div class="card-body p-0">
                    @forelse ($available as $plugin)
                        <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $plugin->name }}</div>
                                <div class="text-muted small">{{ $plugin->description ?? '' }}</div>
                                <div class="mt-1">
                                    <span class="badge bg-secondary">v{{ $plugin->latest_version ?? '—' }}</span>
                                    @if ($plugin->is_installed)
                                        <span class="badge bg-success">Installé</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @php $alreadyInstalled = $plugin->is_installed || $installed->contains('id', $plugin->slug); @endphp
                                @if (!$alreadyInstalled)
                                    <form method="POST" action="{{ route('admin.plugins.install', $plugin->slug) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi bi-cloud-download me-1"></i>Installer
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check-circle me-1"></i>Déjà installé</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-cloud-slash" style="font-size:3rem;"></i>
                            <p class="mt-3">Impossible de contacter MnemoCloud ou aucun plugin disponible.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
