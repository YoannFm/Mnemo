<x-admin-layout>
    <x-slot name="pageTitle">Emojis</x-slot>

    {{-- Onglets --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $type === 'simple' ? 'active' : '' }}"
               href="{{ route('admin.emojis.index', ['type' => 'simple']) }}">
                Emojis simples
                <span class="badge bg-secondary ms-1">{{ \App\Models\Emoji::where('type','simple')->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'animated' ? 'active' : '' }}"
               href="{{ route('admin.emojis.index', ['type' => 'animated']) }}">
                Emojis animés
                <span class="badge bg-secondary ms-1">{{ \App\Models\Emoji::where('type','animated')->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="row g-4">
        {{-- Formulaire d'ajout --}}
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header">
                    <strong>Ajouter un emoji {{ $type === 'animated' ? 'animé' : 'simple' }}</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.emojis.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">

                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="ex: coeur_rouge" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image
                                <small class="text-muted">
                                    ({{ $type === 'animated' ? 'GIF, WEBP animé' : 'PNG, JPG, GIF, WEBP' }}, max 2Mo)
                                </small>
                            </label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                   accept="{{ $type === 'animated' ? 'image/gif,image/webp' : 'image/png,image/jpeg,image/gif,image/webp' }}"
                                   required>
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Grille des emojis --}}
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    @if($emojis->isEmpty())
                        <p class="text-muted text-center py-4">Aucun emoji {{ $type === 'animated' ? 'animé' : 'simple' }} pour l'instant.</p>
                    @else
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($emojis as $emoji)
                                <div class="text-center" style="width:72px;">
                                    <div class="border rounded p-1 mb-1" style="height:56px;display:flex;align-items:center;justify-content:center;">
                                        <img src="{{ $emoji->imageUrl() }}" alt="{{ $emoji->name }}"
                                             style="max-width:40px;max-height:40px;object-fit:contain;">
                                    </div>
                                    <small class="d-block text-truncate text-muted" style="font-size:.7rem;" title="{{ $emoji->name }}">
                                        {{ $emoji->name }}
                                    </small>
                                    <small class="d-block text-muted" style="font-size:.65rem;">:{{ $emoji->slug }}:</small>
                                    <form action="{{ route('admin.emojis.destroy', $emoji) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cet emoji ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm text-danger p-0 mt-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">{{ $emojis->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
