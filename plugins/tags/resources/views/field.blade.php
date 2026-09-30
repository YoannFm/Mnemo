{{-- Tags field for module create/edit forms --}}
@if (isset($allTags))
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
                        <button type="button"
                                class="btn-close btn-close-white"
                                style="font-size:.6rem;"
                                title="Retirer ce tag"
                                onclick="tagsDetach('{{ route('modules.tags.detach', [$module, $tag]) }}', this)"></button>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Add an existing tag --}}
        @php $availableTags = $allTags->whereNotIn('id', $moduleTags->pluck('id')); @endphp
        @if ($availableTags->isNotEmpty())
            <div class="d-flex gap-2 align-items-center mb-2">
                <select id="tag-attach-select-{{ $module->id }}" class="form-select form-select-sm" style="max-width:220px;">
                    <option value="">- Ajouter un tag existant -</option>
                    @foreach ($availableTags as $tag)
                        <option value="{{ $tag->id }}" data-color="{{ $tag->color ?? '#6b7280' }}" data-name="{{ $tag->name }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="tagsAttach({{ $module->id }}, '{{ route('modules.tags.attach', $module) }}')">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
            </div>
        @endif

        {{-- Create a new tag --}}
        <div class="d-flex gap-2 align-items-center" style="margin-top:.25rem;">
            <input type="text" id="new-tag-name-{{ $module->id }}" class="form-control form-control-sm" placeholder="Nouveau tag..." style="max-width:180px;" maxlength="50">
            <input type="color" id="new-tag-color-{{ $module->id }}" value="#6b7280" style="width:36px;height:31px;border:1px solid var(--card-border);border-radius:6px;padding:2px;background:var(--card-bg);cursor:pointer;">
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="tagsCreateNew({{ $module->id }}, '{{ route('tags.store') }}', '{{ route('modules.tags.attach', $module) }}')">
                <i class="bi bi-plus-circle me-1"></i>Créer
            </button>
        </div>

    @else
        {{-- Create mode: select tags to attach (submitted as hidden inputs via JS) --}}
        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem;">
            Sélectionnez un ou plusieurs tags pour ce module.
        </p>
        <div id="tags-create-selected" class="d-flex flex-wrap gap-2 mb-2"></div>
        <div class="d-flex gap-2 align-items-center">
            <select id="tags-create-select" class="form-select form-select-sm" style="max-width:220px;">
                <option value="">- Choisir un tag -</option>
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
                var badge = document.createElement('span');
                badge.className = 'badge d-inline-flex align-items-center gap-1';
                badge.style.cssText = 'background-color:' + color + ';color:#fff;font-size:.8rem;padding:.35em .65em;';
                badge.dataset.id = id;
                badge.textContent = name + ' ';
                var btn = document.createElement('button');
                btn.type = 'button'; btn.className = 'btn-close btn-close-white'; btn.style.fontSize = '.6rem';
                btn.onclick = function () { tagsRemove(id); };
                badge.appendChild(btn);
                document.getElementById('tags-create-selected').appendChild(badge);
                var input = document.createElement('input');
                input.type = 'hidden'; input.name = 'tag_ids[]'; input.value = id; input.id = 'tag-input-' + id;
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

<script>
window.tagsAttach = function(moduleId, attachUrl) {
    var sel = document.getElementById('tag-attach-select-' + moduleId);
    var tagId = sel ? sel.value : '';
    if (!tagId) return;
    var opt = sel.options[sel.selectedIndex];
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(attachUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token},
        body: '_token=' + encodeURIComponent(token) + '&tag_id=' + encodeURIComponent(tagId)
    }).then(function(r) {
        if (r.ok) {
            // Add badge in existing tags area
            var color = opt.dataset.color || '#6b7280';
            var name = opt.dataset.name || opt.text;
            var detachUrl = attachUrl.replace('/tags', '/tags/' + tagId).replace('POST', '');
            var container = document.querySelector('#tags-attached-' + moduleId);
            if (container) {
                var badge = document.createElement('span');
                badge.className = 'badge d-inline-flex align-items-center gap-1';
                badge.style.cssText = 'background-color:' + color + ';color:#fff;font-size:.8rem;padding:.35em .65em;';
                badge.innerHTML = name + ' ';
                container.appendChild(badge);
            }
            // Remove from select
            opt.remove();
            sel.value = '';
            if (sel.options.length <= 1) sel.closest('.d-flex').style.display = 'none';
            // Reload to reflect changes properly
            window.location.reload();
        } else {
            alert('Erreur lors de l\'ajout du tag.');
        }
    }).catch(function() { alert('Erreur réseau.'); });
};

window.tagsDetach = function(url, btn) {
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token},
        body: '_method=DELETE&_token=' + encodeURIComponent(token)
    }).then(function(r) {
        if (r.ok) {
            var badge = btn.closest('.badge');
            if (badge) badge.remove();
        } else {
            alert('Erreur lors de la suppression du tag.');
        }
    }).catch(function() { alert('Erreur réseau.'); });
};

window.tagsCreateNew = function(moduleId, storeUrl, attachUrl) {
    var nameInput = document.getElementById('new-tag-name-' + moduleId);
    var colorInput = document.getElementById('new-tag-color-' + moduleId);
    var name = nameInput ? nameInput.value.trim() : '';
    if (!name) return;
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(storeUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
        body: JSON.stringify({name: name, color: colorInput ? colorInput.value : '#6b7280'})
    }).then(function(r) {
        return r.json().then(function(data) { return {ok: r.ok, data: data}; });
    }).then(function(res) {
        if (!res.ok) { alert(res.data.error || 'Erreur lors de la création du tag.'); return; }
        var tag = res.data;
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = attachUrl;
        var t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = token;
        var id = document.createElement('input'); id.type = 'hidden'; id.name = 'tag_id'; id.value = tag.id;
        form.appendChild(t); form.appendChild(id);
        document.body.appendChild(form);
        form.submit();
    }).catch(function() { alert('Erreur réseau. Réessayez.'); });
};
</script>
@endif
