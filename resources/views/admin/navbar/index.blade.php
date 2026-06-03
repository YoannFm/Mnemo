<x-admin-layout>
    <x-slot name="pageTitle">Navigation</x-slot>

    <div class="row">
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-plus-lg me-1"></i> Ajouter un lien</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.navbar.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Libellé *</label>
                            <input type="text" name="label" class="form-control"
                                   value="{{ old('label') }}" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL *</label>
                            <input type="text" name="url" class="form-control"
                                   value="{{ old('url') }}" required maxlength="255"
                                   placeholder="/bibliotheque">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icône Bootstrap Icons</label>
                            <input type="text" name="icon" class="form-control"
                                   value="{{ old('icon') }}" maxlength="100"
                                   placeholder="bi-globe2">
                            <div class="form-text">Ex : bi-house, bi-collection, bi-book</div>
                        </div>
                        <div class="mb-3 d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="is_active" id="is_active" value="1"
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="open_new_tab" id="open_new_tab" value="1"
                                       {{ old('open_new_tab') ? 'checked' : '' }}>
                                <label class="form-check-label" for="open_new_tab">Nouvel onglet</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Éléments de navigation</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Ordre</th>
                                    <th>Libellé</th>
                                    <th>URL</th>
                                    <th>Icône</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($navItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if (! $loop->first)
                                                    <form method="POST" action="{{ route('admin.navbar.update', $item) }}">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="label" value="{{ $item->label }}">
                                                        <input type="hidden" name="url" value="{{ $item->url }}">
                                                        <input type="hidden" name="icon" value="{{ $item->icon }}">
                                                        <input type="hidden" name="position" value="{{ $item->position - 1 }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Monter">
                                                            <i class="bi bi-arrow-up"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (! $loop->last)
                                                    <form method="POST" action="{{ route('admin.navbar.update', $item) }}">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="label" value="{{ $item->label }}">
                                                        <input type="hidden" name="url" value="{{ $item->url }}">
                                                        <input type="hidden" name="icon" value="{{ $item->icon }}">
                                                        <input type="hidden" name="position" value="{{ $item->position + 1 }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Descendre">
                                                            <i class="bi bi-arrow-down"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                        <td><strong>{{ $item->label }}</strong></td>
                                        <td class="text-muted small">{{ $item->url }}</td>
                                        <td>
                                            @if ($item->icon)
                                                <i class="bi {{ $item->icon }}"></i>
                                                <small class="text-muted ms-1">{{ $item->icon }}</small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-secondary">Inactif</span>
                                            @endif
                                            @if ($item->open_new_tab)
                                                <i class="bi bi-box-arrow-up-right ms-1 text-muted small" title="Nouvel onglet"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="#" class="mx-1" title="Modifier"
                                               data-bs-toggle="modal"
                                               data-bs-target="#edit-modal-{{ $item->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="{{ route('admin.navbar.destroy', $item) }}" class="mx-1 text-danger"
                                               title="Supprimer" data-bs-toggle="tooltip"
                                               onclick="event.preventDefault(); if(confirm('Supprimer ce lien ?')) { document.getElementById('del-nav-{{ $item->id }}').submit(); }">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <form id="del-nav-{{ $item->id }}" method="POST"
                                                  action="{{ route('admin.navbar.destroy', $item) }}" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- Modal édition --}}
                                    <div class="modal fade" id="edit-modal-{{ $item->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modifier "{{ $item->label }}"</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.navbar.update', $item) }}">
                                                    @csrf @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Libellé *</label>
                                                            <input type="text" name="label" class="form-control"
                                                                   value="{{ $item->label }}" required maxlength="100">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">URL *</label>
                                                            <input type="text" name="url" class="form-control"
                                                                   value="{{ $item->url }}" required maxlength="255">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Icône Bootstrap Icons</label>
                                                            <input type="text" name="icon" class="form-control"
                                                                   value="{{ $item->icon }}" maxlength="100">
                                                        </div>
                                                        <div class="d-flex gap-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="is_active" value="1"
                                                                       {{ $item->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label">Actif</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="open_new_tab" value="1"
                                                                       {{ $item->open_new_tab ? 'checked' : '' }}>
                                                                <label class="form-check-label">Nouvel onglet</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Aucun lien de navigation.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
