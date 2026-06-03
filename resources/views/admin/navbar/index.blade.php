<x-admin-layout>
    <x-slot name="pageTitle">Navigation</x-slot>

    <div class="row g-4">

        {{-- Add form --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-plus-lg me-2"></i>Ajouter un lien</div>
                <div class="card-body p-3">
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
                            <label class="form-label">Icône Bootstrap</label>
                            <input type="text" name="icon" class="form-control"
                                   value="{{ old('icon') }}" maxlength="100"
                                   placeholder="bi-globe2">
                        </div>
                        <div class="mb-3 d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="is_active" id="is_active" value="1"
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active"
                                       style="font-size:.8rem;text-transform:none;color:var(--text-muted);">
                                    Actif
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="open_new_tab" id="open_new_tab" value="1"
                                       {{ old('open_new_tab') ? 'checked' : '' }}>
                                <label class="form-check-label" for="open_new_tab"
                                       style="font-size:.8rem;text-transform:none;color:var(--text-muted);">
                                    Nouvel onglet
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-list-ul me-2"></i>Éléments de navigation</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pos.</th>
                                <th>Libellé</th>
                                <th>URL</th>
                                <th>Icône</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="navbar-list">
                            @forelse ($navItems as $item)
                                <tr id="nav-row-{{ $item->id }}">
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if (! $loop->first)
                                                <form method="POST" action="{{ route('admin.navbar.update', $item) }}">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="label" value="{{ $item->label }}">
                                                    <input type="hidden" name="url" value="{{ $item->url }}">
                                                    <input type="hidden" name="icon" value="{{ $item->icon }}">
                                                    <input type="hidden" name="position" value="{{ $item->position - 1 }}">
                                                    <button type="submit" class="btn btn-sm" style="padding:.1rem .3rem;background:none;border:1px solid var(--card-border);color:var(--text-muted);" title="Monter">
                                                        <i class="bi bi-arrow-up" style="font-size:.7rem;"></i>
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
                                                    <button type="submit" class="btn btn-sm" style="padding:.1rem .3rem;background:none;border:1px solid var(--card-border);color:var(--text-muted);" title="Descendre">
                                                        <i class="bi bi-arrow-down" style="font-size:.7rem;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $item->label }}</td>
                                    <td class="text-muted" style="font-size:.8rem;">{{ $item->url }}</td>
                                    <td>
                                        @if ($item->icon)
                                            <i class="bi {{ $item->icon }}" style="color:var(--accent);"></i>
                                            <small class="text-muted ms-1">{{ $item->icon }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->is_active)
                                            <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--success-color);">Actif</span>
                                        @else
                                            <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);">Inactif</span>
                                        @endif
                                        @if ($item->open_new_tab)
                                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.7rem;color:var(--text-muted);" title="Nouvel onglet"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            {{-- Edit modal trigger --}}
                                            <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#edit-modal-{{ $item->id }}"
                                                    title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.navbar.destroy', $item) }}"
                                                  onsubmit="return confirm('Supprimer ce lien ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Edit modal --}}
                                <div class="modal fade" id="edit-modal-{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);">
                                            <div class="modal-header" style="border-color:var(--card-border);">
                                                <h6 class="modal-title" style="font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
                                                    Modifier "{{ $item->label }}"
                                                </h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                                                        <label class="form-label">Icône Bootstrap</label>
                                                        <input type="text" name="icon" class="form-control"
                                                               value="{{ $item->icon }}" maxlength="100">
                                                    </div>
                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="is_active" value="1"
                                                                   {{ $item->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                   style="font-size:.8rem;text-transform:none;color:var(--text-muted);">Actif</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="open_new_tab" value="1"
                                                                   {{ $item->open_new_tab ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                   style="font-size:.8rem;text-transform:none;color:var(--text-muted);">Nouvel onglet</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer" style="border-color:var(--card-border);">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
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
</x-admin-layout>
