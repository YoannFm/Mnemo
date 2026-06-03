{{--
    Autocomplete multi-utilisateurs avec tags.
    Variables attendues :
      $inputId     - ID unique pour éviter les conflits si plusieurs instances
      $allUsers    - collection/array de tous les users {id, name, email}
      $presetUsers - collection des users pre-selectionnes (optionnel)
--}}
@php
    $inputId     = $inputId ?? 'userSearch';
    $allUsers    = collect($allUsers ?? []);
    $presetUsers = collect($presetUsers ?? []);
@endphp

<div class="user-autocomplete-wrapper position-relative" data-input-id="{{ $inputId }}">

    {{-- Tags des utilisateurs selectionnes --}}
    <div class="user-tags d-flex flex-wrap gap-1 mb-2" id="{{ $inputId }}Tags">
        @foreach($presetUsers as $u)
            <span class="badge bg-secondary d-flex align-items-center gap-1 user-tag"
                  data-id="{{ $u->id }}">
                {{ $u->name }}
                <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                        aria-label="Retirer" style="font-size:.6rem"></button>
                <input type="hidden" name="user_ids[]" value="{{ $u->id }}">
            </span>
        @endforeach
    </div>

    {{-- Champ de recherche --}}
    <input type="text" class="form-control" id="{{ $inputId }}"
           placeholder="Rechercher un utilisateur..." autocomplete="off">

    {{-- Dropdown suggestions --}}
    <ul class="list-group position-absolute w-100 shadow-sm"
        id="{{ $inputId }}Dropdown"
        style="display:none;z-index:1060;max-height:200px;overflow-y:auto;top:100%">
    </ul>
</div>

{{-- Données users en JSON pour ce composant --}}
<script>
(function () {
    var inputId  = {{ Js::from($inputId) }};
    var allUsers = {{ Js::from($allUsers->values()) }};

    var wrapper   = document.querySelector('[data-input-id="' + inputId + '"]');
    var tagsEl    = document.getElementById(inputId + 'Tags');
    var searchEl  = document.getElementById(inputId);
    var dropdown  = document.getElementById(inputId + 'Dropdown');

    function selectedIds() {
        return Array.from(tagsEl.querySelectorAll('.user-tag')).map(function (t) {
            return parseInt(t.dataset.id, 10);
        });
    }

    function addTag(user) {
        if (selectedIds().includes(user.id)) return;
        var span = document.createElement('span');
        span.className = 'badge bg-secondary d-flex align-items-center gap-1 user-tag';
        span.dataset.id = user.id;
        span.innerHTML =
            user.name +
            '<button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Retirer" style="font-size:.6rem"></button>' +
            '<input type="hidden" name="user_ids[]" value="' + user.id + '">';
        span.querySelector('button').addEventListener('click', function () {
            span.remove();
        });
        tagsEl.appendChild(span);
    }

    // Retirer les tags pre-selectionnes
    tagsEl.querySelectorAll('.user-tag button').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.user-tag').remove(); });
    });

    searchEl.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        dropdown.innerHTML = '';
        if (!q) { dropdown.style.display = 'none'; return; }

        var ids = selectedIds();
        var matches = allUsers.filter(function (u) {
            return !ids.includes(u.id) &&
                   (u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
        }).slice(0, 8);

        if (!matches.length) { dropdown.style.display = 'none'; return; }

        matches.forEach(function (u) {
            var li = document.createElement('li');
            li.className = 'list-group-item list-group-item-action py-1 px-2';
            li.style.cursor = 'pointer';
            li.innerHTML = '<strong>' + u.name + '</strong> <small class="text-muted">' + u.email + '</small>';
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                addTag(u);
                searchEl.value = '';
                dropdown.style.display = 'none';
            });
            dropdown.appendChild(li);
        });
        dropdown.style.display = '';
    });

    searchEl.addEventListener('blur', function () {
        setTimeout(function () { dropdown.style.display = 'none'; }, 150);
    });
    searchEl.addEventListener('focus', function () {
        if (this.value.trim()) this.dispatchEvent(new Event('input'));
    });
})();
</script>
