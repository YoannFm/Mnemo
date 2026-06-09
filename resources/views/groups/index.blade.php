<x-app-layout>
    <x-slot name="pageTitle">Mes groupes</x-slot>

    @if (session('success'))
        <div class="alert alert-success mb-3" style="font-size:.875rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-warning mb-3" style="font-size:.875rem;">{{ session('error') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1">Mes groupes</h4>
            <p style="color:var(--text-muted);font-size:.875rem;">Gérez vos classes et groupes d'élèves.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
            <i class="bi bi-plus-lg me-1"></i>Nouveau groupe
        </button>
    </div>

    {{-- Groupes que je gère --}}
    @if ($groups->isNotEmpty())
        <h5 class="mb-3">Groupes que je gère</h5>
        <div class="row g-3 mb-5">
            @foreach ($groups as $group)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 p-3">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <div class="fw-semibold">{{ $group->name }}</div>
                                <div style="font-size:.78rem;color:var(--text-muted);">
                                    <i class="bi bi-people me-1"></i>{{ $group->members_count }} membre{{ $group->members_count > 1 ? 's' : '' }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('groups.destroy', $group) }}"
                                  onsubmit="return confirm('Supprimer ce groupe ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="color:#ef4444;border:1px solid var(--card-border);">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @if ($group->description)
                            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">{{ $group->description }}</p>
                        @endif
                        <a href="{{ route('groups.show', $group) }}" class="btn btn-sm btn-outline-primary mt-auto">
                            <i class="bi bi-people me-1"></i>Gérer les membres
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Groupes dont je suis membre --}}
    @if ($memberOf->isNotEmpty())
        <h5 class="mb-3">Groupes dont je suis membre</h5>
        <div class="row g-3">
            @foreach ($memberOf as $group)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 p-3">
                        <div class="fw-semibold mb-1">{{ $group->name }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted);">
                            <i class="bi bi-person me-1"></i>Géré par {{ $group->owner->name }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-people me-1"></i>{{ $group->members_count }} membre{{ $group->members_count > 1 ? 's' : '' }}
                        </div>
                        @if ($group->description)
                            <p style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem;margin-bottom:0;">{{ $group->description }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($groups->isEmpty() && $memberOf->isEmpty())
        <div class="card text-center py-5">
            <i class="bi bi-people" style="font-size:3rem;color:var(--text-muted);"></i>
            <h6 class="mt-3">Aucun groupe</h6>
            <p style="color:var(--text-muted);font-size:.875rem;">Créez un groupe pour organiser vos élèves.</p>
        </div>
    @endif

    {{-- Modal création groupe --}}
    <div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);">
                <div class="modal-header" style="border-bottom:1px solid var(--card-border);">
                    <h5 class="modal-title"><i class="bi bi-people me-2"></i>Nouveau groupe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('groups.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="group_name">Nom du groupe <span class="text-danger">*</span></label>
                            <input type="text" id="group_name" name="name" class="form-control" required maxlength="255" placeholder="Ex: Terminale A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="group_desc">Description <span style="font-size:.8rem;color:var(--text-muted);">(optionnel)</span></label>
                            <textarea id="group_desc" name="description" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid var(--card-border);">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="color:var(--text-muted);border:1px solid var(--card-border);">Annuler</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
