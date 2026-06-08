<x-admin-layout>
    <x-slot name="pageTitle">Themes</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Gestion des themes</h4>
        <a href="{{ route('admin.themes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouveau theme
        </a>
    </div>

    <div class="row g-3">
        @forelse($themes as $theme)
            <div class="col-md-4">
                <div class="card shadow h-100 {{ $theme->is_active ? 'border-success border-2' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $theme->name }}</strong>
                        @if($theme->is_active)
                            <span class="badge bg-success">Actif</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-3 flex-wrap">
                            @foreach([
                                'Fond' => $theme->body_bg,
                                'Contenu' => $theme->content_bg,
                                'Carte' => $theme->card_bg,
                                'Accent' => $theme->accent_color,
                                'Header' => $theme->header_bg,
                                'Texte' => $theme->text_color,
                            ] as $label => $color)
                                <div title="{{ $label }}: {{ $color }}"
                                     style="width:28px;height:28px;background:{{ $color }};border:1px solid #999;border-radius:4px;"
                                     data-bs-toggle="tooltip"></div>
                            @endforeach
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            @if(!$theme->is_active)
                                <form action="{{ route('admin.themes.activate', $theme) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-check-circle"></i> Activer
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.themes.edit', $theme) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <form action="{{ route('admin.themes.duplicate', $theme) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Dupliquer">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </form>
                            @if(!$theme->is_active)
                                <form action="{{ route('admin.themes.destroy', $theme) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce theme ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted">Aucun theme disponible.</p>
            </div>
        @endforelse
    </div>
</x-admin-layout>
