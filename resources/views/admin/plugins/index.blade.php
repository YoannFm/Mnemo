<x-admin-layout>
    <x-slot name="pageTitle">Plugins</x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-puzzle me-2"></i>Plugins installés</h5>
            <form method="POST" action="{{ route('admin.plugins.reload') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Recharger
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            @forelse ($plugins as $plugin)
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
                    <p class="mt-3">Aucun plugin installé.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
