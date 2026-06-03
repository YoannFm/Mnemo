@csrf

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label" for="label">Nom <span class="text-danger">*</span></label>
        <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror"
               value="{{ old('label', $navItem->label ?? '') }}" required maxlength="100">
        @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="icon">Icone</label>
        <div class="input-group">
            <span class="input-group-text">
                <i id="icon-preview" class="{{ old('icon', $navItem->icon ?? 'bi bi-question') }}"></i>
            </span>
            <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror"
                   value="{{ old('icon', $navItem->icon ?? '') }}" maxlength="100"
                   placeholder="bi bi-house">
            @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="form-text">Ex : bi bi-house, bi bi-collection</div>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" {{ old('type', $navItem->type ?? 'link') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Champ conditionnel selon le type --}}
<div class="mb-3" id="field-link" style="display:none;">
    <label class="form-label" for="value-link">URL</label>
    <input type="text" name="value" id="value-link" class="form-control"
           value="{{ old('value', $navItem->value ?? '') }}" maxlength="500" placeholder="/ma-page">
</div>

<div class="mb-3" id="field-page" style="display:none;">
    <label class="form-label" for="value-page">Page</label>
    <select name="value" id="value-page" class="form-select">
        <option value="">-- Choisir une page --</option>
        @foreach($pages as $page)
            <option value="{{ $page->slug }}"
                {{ old('value', $navItem->value ?? '') === $page->slug ? 'selected' : '' }}>
                {{ $page->title }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3" id="field-post" style="display:none;">
    <label class="form-label" for="value-post">Article</label>
    <select name="value" id="value-post" class="form-select">
        <option value="">-- Choisir un article --</option>
        @foreach($posts as $post)
            <option value="{{ $post->slug }}"
                {{ old('value', $navItem->value ?? '') === $post->slug ? 'selected' : '' }}>
                {{ $post->title }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3" id="field-dropdown" style="display:none;">
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Les sous-elements peuvent etre ajoutes apres creation depuis la liste de navigation.
    </div>
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="new_tab" id="new_tab" value="1"
               {{ old('new_tab', $navItem->new_tab ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="new_tab">Ouvrir dans un nouvel onglet</label>
    </div>
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
               {{ old('is_active', $navItem->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Actif</label>
    </div>
</div>

<script>
    (function () {
        const typeSelect   = document.getElementById('type');
        const iconInput    = document.getElementById('icon');
        const iconPreview  = document.getElementById('icon-preview');

        const fields = {
            link:     document.getElementById('field-link'),
            page:     document.getElementById('field-page'),
            post:     document.getElementById('field-post'),
            dropdown: document.getElementById('field-dropdown'),
        };

        function showField(type) {
            Object.keys(fields).forEach(function (key) {
                fields[key].style.display = key === type ? '' : 'none';
            });
        }

        showField(typeSelect.value);

        typeSelect.addEventListener('change', function () {
            showField(this.value);
        });

        iconInput.addEventListener('input', function () {
            iconPreview.className = this.value.trim() || 'bi bi-question';
        });
    })();
</script>
