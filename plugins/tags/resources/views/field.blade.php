{{-- Tags field for module create/edit forms --}}
@if (isset($allTags) && $allTags->isNotEmpty())
<div class="mb-4 p-3" style="border:1px solid var(--card-border);border-radius:8px;">
    <label class="form-label fw-semibold" style="font-size:.875rem;">
        <i class="bi bi-tags me-1" style="color:var(--accent);"></i>
        Tags
    </label>

    @if (isset($module) && $module !== null)
        {{-- Edit mode: show existing tags with remove buttons --}}
        @php $moduleTags = $module->tags ?? collect(); @endphp
        @if ($moduleTags->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach ($moduleTags as $tag)
                    <span class="badge d-inline-flex align-items-center gap-1"
                          style="background-color:{{ $tag->color ?? '#6b7280' }};color:#fff;font-size:.8rem;padding:.35em .65em;">
                        {{ $tag->name }}
                        <form method="POST"
                              action="{{ route('modules.tags.detach', [$module, $tag]) }}"
                              style="display:inline;margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn-close btn-close-white"
                                    style="font-size:.6rem;"
                                    title="Retirer ce tag"></button>
                        </form>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Add a new tag --}}
        @php $availableTags = $allTags->whereNotIn('id', $moduleTags->pluck('id')); @endphp
        @if ($availableTags->isNotEmpty())
            <form method="POST" action="{{ route('modules.tags.attach', $module) }}" class="d-flex gap-2 align-items-center">
                @csrf
                <select name="tag_id" class="form-select form-select-sm" style="max-width:220px;">
                    <option value="">— Ajouter un tag —</option>
                    @foreach ($availableTags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
            </form>
        @endif

    @else
        {{-- Create mode: select tags to attach (submitted as hidden inputs via JS) --}}
        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem;">
            Sélectionnez un ou plusieurs tags pour ce module.
        </p>
        <div id="tags-create-selected" class="d-flex flex-wrap gap-2 mb-2"></div>
        <div class="d-flex gap-2 align-items-center">
            <select id="tags-create-select" class="form-select form-select-sm" style="max-width:220px;">
                <option value="">— Choisir un tag —</option>
                @foreach ($allTags as $tag)
                    <option value="{{ $tag->id }}" data-name="{{ $tag->name }}" data-color="{{ $tag->color ?? '#6b7280' }}">
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tagsAddSelected()">
                <i class="bi bi-plus-lg me-1"></i>Ajouter
            </button>
        </div>
        <div id="tags-create-inputs"></div>

        <script>
        (function () {
            var selected = [];

            window.tagsAddSelected = function () {
                var sel = document.getElementById('tags-create-select');
                var id = sel.value;
                if (!id || selected.includes(id)) return;
                selected.push(id);
                var opt = sel.options[sel.selectedIndex];
                var name = opt.dataset.name;
                var color = opt.dataset.color;

                // Badge
                var badge = document.createElement('span');
                badge.className = 'badge d-inline-flex align-items-center gap-1';
                badge.style.cssText = 'background-color:' + color + ';color:#fff;font-size:.8rem;padding:.35em .65em;';
                badge.dataset.id = id;
                badge.textContent = name + ' ';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-close btn-close-white';
                btn.style.fontSize = '.6rem';
                btn.onclick = function () { tagsRemove(id); };
                badge.appendChild(btn);
                document.getElementById('tags-create-selected').appendChild(badge);

                // Hidden input
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tag_ids[]';
                input.value = id;
                input.id = 'tag-input-' + id;
                document.getElementById('tags-create-inputs').appendChild(input);

                sel.value = '';
            };

            window.tagsRemove = function (id) {
                selected = selected.filter(function (x) { return x !== id; });
                var badge = document.querySelector('[data-id="' + id + '"]');
                if (badge) badge.remove();
                var inp = document.getElementById('tag-input-' + id);
                if (inp) inp.remove();
            };
        })();
        </script>
    @endif
</div>
@endif
