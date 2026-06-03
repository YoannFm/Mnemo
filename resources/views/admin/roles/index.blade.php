<x-admin-layout>
    <x-slot name="pageTitle">Rôles</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">

            <ol class="list-unstyled sortable mb-3" id="roles">
                @foreach($roles as $role)
                    <li class="sortable-item sortable-dropdown" data-id="{{ $role->id }}">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <span>
                                    <i class="bi bi-arrows-move sortable-handle" style="cursor:grab;"></i>

                                    <span class="badge" style="{{ $role->getBadgeStyle() }}; font-size: 1.05em">
                                        @if($role->icon) <i class="{{ $role->icon }}"></i> @endif
                                        {{ $role->name }}
                                    </span>

                                    <span class="text-body-secondary">
                                        (ID: {{ $role->id }}, Pouvoir: {{ $role->power }})
                                    </span>

                                    @if(!$role->is_admin_role)
                                        <i class="bi bi-star text-info" title="Rôle par défaut" data-bs-toggle="tooltip"></i>
                                    @else
                                        <i class="bi bi-trophy text-warning" title="Rôle administrateur" data-bs-toggle="tooltip"></i>
                                    @endif
                                </span>
                                <span>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="m-1" title="Modifier" data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce rôle ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 m-1" title="Supprimer" data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>

            <button type="button" class="btn btn-success" id="save">
                <i class="bi bi-floppy"></i> Sauvegarder
            </button>

            <a class="btn btn-primary" href="{{ route('admin.roles.create') }}">
                <i class="bi bi-plus-lg"></i> Ajouter
            </a>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        const sortable = Sortable.create(document.getElementById('roles'), {
            animation: 150,
            handle: '.sortable-handle',
        });

        document.getElementById('save').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            const order = [...document.querySelectorAll('#roles [data-id]')].map(el => el.dataset.id);
            fetch('{{ route('admin.roles.order') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ order }),
            }).then(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Sauvegardé';
                setTimeout(() => btn.innerHTML = '<i class="bi bi-floppy"></i> Sauvegarder', 2000);
            });
        });
    </script>
</x-admin-layout>
