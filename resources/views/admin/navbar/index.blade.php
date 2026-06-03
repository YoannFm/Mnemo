<x-admin-layout>
    <x-slot name="pageTitle">Navigation</x-slot>

    <div class="row">
        {{-- Liste drag & drop --}}
        <div class="col-xl-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Éléments de navigation</h5>
                    <button type="button" id="saveOrder" class="btn btn-success btn-sm">
                        <i class="bi bi-check2-all me-1"></i> Sauvegarder l'ordre
                    </button>
                </div>
                <div class="card-body">
                    <ul id="sortable-nav" class="list-unstyled mb-0" style="min-height:40px;">
                        @forelse ($navItems as $item)
                            <li data-id="{{ $item->id }}"
                                class="d-flex align-items-center gap-2 py-2 px-2 border-bottom">
                                <span class="drag-handle text-muted" style="cursor:grab; font-size:1.2rem;">
                                    <i class="bi bi-grip-vertical"></i>
                                </span>
                                @if ($item->icon)
                                    <span class="badge bg-secondary"><i class="bi {{ $item->icon }}"></i></span>
                                @endif
                                <span class="fw-bold flex-grow-1">{{ $item->label }}</span>
                                <span class="text-muted small">{{ $item->url }}</span>
                                @if ($item->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                                <a href="#" class="btn btn-sm btn-outline-secondary"
                                   data-bs-toggle="modal"
                                   data-bs-target="#edit-modal-{{ $item->id }}"
                                   title="Modifier">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.navbar.destroy', $item) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Supprimer ce lien ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>

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
                            <li class="text-center text-muted py-4">Aucun lien de navigation.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Formulaire d'ajout --}}
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        const el = document.getElementById('sortable-nav');
        const sortable = Sortable.create(el, { handle: '.drag-handle', animation: 150 });

        document.getElementById('saveOrder').addEventListener('click', function () {
            const order = sortable.toArray();
            const btn = this;
            btn.disabled = true;

            fetch('/admin/navbar/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ order })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    const flash = document.createElement('div');
                    flash.className = 'alert alert-success alert-dismissible mt-2';
                    flash.innerHTML = '<i class="bi bi-check-circle me-1"></i> Ordre sauvegardé.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    el.parentElement.insertAdjacentElement('afterend', flash);
                    setTimeout(() => flash.remove(), 3000);
                }
            })
            .catch(() => alert('Erreur lors de la sauvegarde de l\'ordre.'))
            .finally(() => { btn.disabled = false; });
        });
    </script>
</x-admin-layout>
