<x-admin-layout>
    <x-slot name="pageTitle">Corbeille des modules</x-slot>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-trash me-2"></i>Corbeille des modules</h4>
            <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Modules supprimés par les utilisateurs.</p>
        </div>
        <a href="{{ route('admin.modules.index') }}" class="btn btn-sm" style="border:1px solid var(--card-border);">
            <i class="bi bi-arrow-left me-1"></i>Modules publics
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher par titre..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tous les utilisateurs</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    @if($modules->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-trash" style="font-size:3rem;color:var(--text-muted);"></i>
            <h5 class="mt-3">La corbeille est vide</h5>
        </div>
    @else
        <div class="card" style="overflow:hidden;">
            <div class="table-responsive">
                <table class="table mb-0" style="color:inherit;">
                    <thead style="font-size:.8rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                        <tr>
                            <th class="px-3 py-3">Module</th>
                            <th class="px-3 py-3">Propriétaire</th>
                            <th class="px-3 py-3 text-center">Items</th>
                            <th class="px-3 py-3">Supprimé le</th>
                            <th class="px-3 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr style="border-top:1px solid var(--card-border);">
                                <td class="px-3 py-3 align-middle">
                                    <div class="fw-semibold">{{ $module->title }}</div>
                                    @if($module->description)
                                        <div style="font-size:.75rem;color:var(--text-muted);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $module->description }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 align-middle">
                                    {{ $module->owner?->name ?? 'Inconnu' }}
                                </td>
                                <td class="px-3 py-3 align-middle text-center">{{ $module->items_count }}</td>
                                <td class="px-3 py-3 align-middle" style="font-size:.85rem;color:var(--text-muted);">
                                    {{ $module->deleted_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-3 align-middle text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <form method="POST" action="{{ route('admin.modules.restore', $module->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm" style="color:#22c55e;border:1px solid #22c55e;">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.modules.force-delete', $module->id) }}"
                                              onsubmit="return confirm('Supprimer définitivement « {{ addslashes($module->title) }} » ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="color:#ef4444;border:1px solid #ef4444;">
                                                <i class="bi bi-trash me-1"></i>Supprimer
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

        <div class="mt-3">{{ $modules->links() }}</div>
    @endif
</x-admin-layout>
