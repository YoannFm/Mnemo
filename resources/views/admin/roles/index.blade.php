<x-admin-layout>
    <x-slot name="pageTitle">Rôles</x-slot>

    <ul class="list-group mb-3" id="sortable-roles">
        @foreach($roles as $role)
            <li class="list-group-item d-flex justify-content-between align-items-center"
                data-id="{{ $role->id }}">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grip-vertical text-muted" style="cursor:grab;"></i>
                    <span class="badge" style="{{ $role->getBadgeStyle() }}">
                        @if($role->icon) <i class="{{ $role->icon }} me-1"></i> @endif
                        {{ $role->name }}
                    </span>
                    <span class="text-muted small">(ID: {{ $role->id }}, Pouvoir: {{ $role->power }})</span>
                    @if($role->is_admin_role)
                        <i class="bi bi-star-fill text-warning" title="Role administrateur"></i>
                    @else
                        <i class="bi bi-star text-muted" title="Role standard"></i>
                    @endif
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-secondary mx-1" title="Modifier">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Supprimer ce role ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger p-0 mx-1" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-success" id="saveOrder">
            <i class="bi bi-floppy"></i> Sauvegarder
        </button>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        Sortable.create(document.getElementById('sortable-roles'), {
            handle: '.bi-grip-vertical',
            animation: 150,
        });

        document.getElementById('saveOrder').addEventListener('click', function () {
            const order = [...document.querySelectorAll('#sortable-roles [data-id]')].map(el => el.dataset.id);
            fetch('{{ route('admin.roles.index') }}/order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ order }),
            }).then(() => {
                const btn = document.getElementById('saveOrder');
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Sauvegarde';
                setTimeout(() => btn.innerHTML = '<i class="bi bi-floppy"></i> Sauvegarder', 2000);
            });
        });
    </script>
</x-admin-layout>
