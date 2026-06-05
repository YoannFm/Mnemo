<x-admin-layout>
    <x-slot name="pageTitle">Navigation</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <ol class="list-unstyled sortable sortable-list mb-2" id="sortable">
                @foreach($navItems as $navItem)
                    <li class="sortable-item @if($navItem->isDropdown()) sortable-parent @endif" data-id="{{ $navItem->id }}">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <span>
                                    <i class="bi bi-arrows-move sortable-handle" style="cursor:grab;"></i>
                                    @if($navItem->icon) <i class="{{ $navItem->icon }}"></i> @endif
                                    {{ $navItem->label }}
                                    @if($navItem->isDropdown())
                                        <i class="ms-2 bi bi-list"></i>
                                    @endif
                                </span>
                                <span>
                                    <a href="{{ route('admin.navbar.edit', $navItem) }}" class="m-1" title="Modifier" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                    @if(!$navItem->is_protected)
                                    <form action="{{ route('admin.navbar.destroy', $navItem) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 m-1" title="Supprimer" data-bs-toggle="tooltip"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($navItem->isDropdown())
                            <ol class="list-unstyled sortable sortable-list ms-4">
                                @foreach($navItem->children as $child)
                                    <li class="sortable-item" data-id="{{ $child->id }}">
                                        <div class="card">
                                            <div class="card-body d-flex justify-content-between">
                                                <span>
                                                    <i class="bi bi-arrows-move sortable-handle" style="cursor:grab;"></i>
                                                    @if($child->icon) <i class="{{ $child->icon }}"></i> @endif
                                                    {{ $child->label }}
                                                </span>
                                                <span>
                                                    <a href="{{ route('admin.navbar.edit', $child) }}" class="m-1" title="Modifier" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                                    @if(!$child->is_protected)
                                                    <form action="{{ route('admin.navbar.destroy', $child) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger p-0 m-1"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </li>
                @endforeach
            </ol>

            @if($navItems->isNotEmpty())
                <button type="button" class="btn btn-success" id="save">
                    <i class="bi bi-floppy"></i> Sauvegarder
                    <span class="spinner-border spinner-border-sm btn-spinner d-none" role="status"></span>
                </button>
            @endif

            <a class="btn btn-primary" href="{{ route('admin.navbar.create') }}">
                <i class="bi bi-plus-lg"></i> Ajouter
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        const sortable = document.getElementById('sortable');

        document.querySelectorAll('.sortable-list').forEach(function (el) {
            Sortable.create(el, {
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                handle: '.sortable-handle',
                group: {
                    name: 'navbar',
                    put: function (to, from, drag) {
                        return !drag.classList.contains('sortable-parent');
                    },
                },
            });
        });

        function serialize(el) {
            return [...el.children].map(function (child) {
                const nested = child.querySelector('.sortable');
                return {
                    id: child.dataset['id'],
                    children: nested ? serialize(nested) : [],
                };
            });
        }

        const saveBtn = document.getElementById('save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                saveBtn.disabled = true;
                fetch('{{ route('admin.navbar.order') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ order: serialize(sortable) }),
                }).then(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Sauvegarde';
                    setTimeout(() => saveBtn.innerHTML = '<i class="bi bi-floppy"></i> Sauvegarder', 2000);
                });
            });
        }
    </script>
</x-admin-layout>
