<x-admin-layout>
    <x-slot name="pageTitle">Plugins</x-slot>

    {{-- Plugins installés --}}
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-puzzle-fill me-2"></i>Plugins installés</h5>
            <form method="POST" action="{{ route('admin.plugins.reload') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Recharger
                </button>
            </form>
        </div>

        @if ($installed->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Auteur</th>
                        <th>Version</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($installed as $plugin)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $plugin->name }}</span>
                            @if (!empty($plugin->description))
                                <div class="text-muted small">{{ $plugin->description }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $plugin->author ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">v{{ $plugin->version }}</span>
                            @if (!empty($plugin->has_update))
                                <span class="badge bg-warning text-dark ms-1">
                                    <i class="bi bi-arrow-up-circle me-1"></i>v{{ $plugin->latest_version }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($plugin->is_enabled)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Activé</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Désactivé</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
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
                                    <button class="btn btn-sm {{ !empty($plugin->has_update) ? 'btn-warning' : 'btn-outline-secondary' }}" title="{{ !empty($plugin->has_update) ? 'Mettre à jour' : 'Réinstaller' }}">
                                        <i class="bi bi-arrow-up-circle{{ !empty($plugin->has_update) ? '-fill' : '' }}"></i>
                                    </button>
                                </form>
                                @if (!$plugin->is_enabled)
                                <form method="POST" action="{{ route('admin.plugins.delete', $plugin->id) }}"
                                      onsubmit="return confirm('Supprimer ce plugin ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-puzzle" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-0">Aucun plugin installé.<br>Utilisez le tableau ci-dessous pour en installer.</p>
            </div>
        @endif
    </div>

    {{-- Plugins disponibles --}}
    <div class="card shadow">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-cloud me-2"></i>Plugins disponibles</h5>
        </div>

        @if ($available->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Auteur</th>
                        <th>Version</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($available as $plugin)
                    @php
                        $alreadyInstalled = $plugin->is_installed
                            || $installed->contains('id', $plugin->slug)
                            || $installed->contains(fn($p) => strtolower($p->id) === strtolower($plugin->slug));
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $plugin->name }}</td>
                        <td class="text-muted small">{{ $plugin->description ?? '' }}</td>
                        <td class="text-muted small">{{ $plugin->author ?? '-' }}</td>
                        <td><span class="badge bg-secondary">v{{ $plugin->latest_version ?? '-' }}</span></td>
                        <td class="text-end">
                            @if ($alreadyInstalled)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Installé</span>
                            @else
                                <form method="POST" action="{{ route('admin.plugins.install', $plugin->slug) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-cloud-download me-1"></i>Installer
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-cloud-slash" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-0">Impossible de contacter MnemoCloud ou aucun plugin disponible.</p>
            </div>
        @endif
    </div>
</x-admin-layout>
