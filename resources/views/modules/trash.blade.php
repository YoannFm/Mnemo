<x-app-layout>
    <x-slot name="pageTitle">Corbeille</x-slot>

    {{-- ── En-tête ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-trash me-2"></i>Corbeille</h4>
            <p style="color:var(--text-muted);font-size:.85rem;margin:0;">
                Modules supprimés - restaurez-les ou supprimez-les définitivement.
            </p>
        </div>
        <a href="{{ route('modules.index') }}" class="btn"
           style="color:var(--accent);border:1px solid var(--card-border);">
            <i class="bi bi-arrow-left me-1"></i> Retour à mes modules
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($modules->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-trash" style="font-size:3.5rem;color:var(--text-muted);"></i>
            <h5 class="mt-3">La corbeille est vide</h5>
            <p style="color:var(--text-muted);font-size:.875rem;">
                Les modules supprimés apparaissent ici avant leur suppression définitive.
            </p>
            <a href="{{ route('modules.index') }}" class="btn btn-primary mx-auto" style="width:fit-content;">
                <i class="bi bi-collection me-1"></i> Mes modules
            </a>
        </div>
    @else
        <div class="card" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:.75rem;overflow:hidden;">
            <div class="table-responsive">
                <table class="table mb-0" style="color:inherit;">
                    <thead style="background:rgba(0,0,0,.15);font-size:.8rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        <tr>
                            <th class="px-3 py-3">Module</th>
                            <th class="px-3 py-3 text-center" style="white-space:nowrap;">Items</th>
                            <th class="px-3 py-3" style="white-space:nowrap;">Supprimé le</th>
                            <th class="px-3 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $module)
                            <tr style="border-top:1px solid var(--card-border);">
                                <td class="px-3 py-3 align-middle">
                                    <div class="fw-semibold">{{ $module->title }}</div>
                                    @if ($module->description)
                                        <div style="font-size:.78rem;color:var(--text-muted);
                                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px;">
                                            {{ $module->description }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 align-middle text-center">
                                    <span style="font-size:.85rem;color:var(--text-muted);">
                                        <i class="bi bi-card-list me-1"></i>{{ $module->items_count }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 align-middle" style="font-size:.85rem;color:var(--text-muted);white-space:nowrap;">
                                    {{ $module->deleted_at->format('d/m/Y à H:i') }}
                                </td>
                                <td class="px-3 py-3 align-middle text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        {{-- Restaurer --}}
                                        <form method="POST" action="{{ route('modules.restore', $module->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm"
                                                    style="color:#22c55e;border:1px solid #22c55e;background:transparent;white-space:nowrap;">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer
                                            </button>
                                        </form>
                                        {{-- Supprimer définitivement --}}
                                        <form method="POST" action="{{ route('modules.force-delete', $module->id) }}"
                                              onsubmit="return confirm('Supprimer définitivement « {{ addslashes($module->title) }} » ? Cette action est irréversible.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm"
                                                    style="color:#ef4444;border:1px solid #ef4444;background:transparent;white-space:nowrap;">
                                                <i class="bi bi-trash me-1"></i>Supprimer définitivement
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-app-layout>
