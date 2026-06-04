@csrf
<div class="mb-3">
    <label class="form-label" for="themeName">Nom</label>
    <input type="color" class="form-control form-control-color d-none" id="accentColor" name="accent_color"
           value="{{ old('accent_color', $theme->accent_color ?? '#EFB702') }}">
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="themeName" name="name" value="{{ old('name', $theme->name ?? '') }}" required>
    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="accentColorVisible">Couleur accent</label>
    <input type="color" class="form-control form-control-color" id="accentColorVisible"
           value="{{ old('accent_color', $theme->accent_color ?? '#EFB702') }}"
           oninput="document.getElementById('accentColor').value=this.value">
</div>

<ul class="nav nav-tabs mb-3" id="themeModeTabs">
    <li class="nav-item">
        <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#darkTab">
            <i class="bi bi-moon-fill me-1"></i> Mode sombre
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#lightTab">
            <i class="bi bi-sun-fill me-1"></i> Mode clair
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- Mode sombre --}}
    <div class="tab-pane fade show active" id="darkTab">
        <div class="row g-3">
            @foreach([
                ['bodyBg',    'body_bg',    'Fond principal', '#111113'],
                ['contentBg', 'content_bg', 'Fond contenu',   '#2E2E34'],
                ['cardBg',    'card_bg',    'Fond carte',     '#212227'],
                ['headerBg',  'header_bg',  'Fond header',    '#1a1b1f'],
                ['textColor', 'text_color', 'Couleur texte',  '#e2e8f0'],
            ] as [$id, $name, $label, $default])
                <div class="col-md-4">
                    <label class="form-label" for="{{ $id }}">{{ $label }}</label>
                    <input type="color" class="form-control form-control-color dark-color" id="{{ $id }}" name="{{ $name }}"
                           value="{{ old($name, $theme->$name ?? $default) }}"
                           data-light-target="{{ 'light_' . $name }}">
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mt-3" id="generateLight">
            <i class="bi bi-arrow-right-circle me-1"></i> Générer le mode clair depuis ces couleurs
        </button>
    </div>

    {{-- Mode clair --}}
    <div class="tab-pane fade" id="lightTab">
        <div class="row g-3">
            @foreach([
                ['lightBodyBg',    'light_body_bg',    'Fond principal', '#f0f2f5'],
                ['lightContentBg', 'light_content_bg', 'Fond contenu',   '#e9ecef'],
                ['lightCardBg',    'light_card_bg',    'Fond carte',     '#ffffff'],
                ['lightHeaderBg',  'light_header_bg',  'Fond header',    '#ffffff'],
                ['lightTextColor', 'light_text_color', 'Couleur texte',  '#212529'],
            ] as [$id, $name, $label, $default])
                <div class="col-md-4">
                    <label class="form-label" for="{{ $id }}">{{ $label }}</label>
                    <input type="color" class="form-control form-control-color" id="{{ $id }}" name="{{ $name }}"
                           value="{{ old($name, $theme->$name ?? $default) }}">
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-3"></div>

<script>
document.getElementById('generateLight').addEventListener('click', function () {
    document.querySelectorAll('.dark-color').forEach(function (input) {
        var lightTarget = input.dataset.lightTarget;
        var lightInput = document.querySelector('[name="' + lightTarget + '"]');
        if (!lightInput) return;
        lightInput.value = invertColor(input.value);
    });
    // Switch to light tab
    var lightTabBtn = document.querySelector('[data-bs-target="#lightTab"]');
    if (lightTabBtn) bootstrap.Tab.getOrCreateInstance(lightTabBtn).show();
});

function invertColor(hex) {
    var r = parseInt(hex.slice(1,3), 16);
    var g = parseInt(hex.slice(3,5), 16);
    var b = parseInt(hex.slice(5,7), 16);
    // Lighten: mix with white
    r = Math.min(255, Math.round(r * 0.1 + 245));
    g = Math.min(255, Math.round(g * 0.1 + 245));
    b = Math.min(255, Math.round(b * 0.1 + 245));
    return '#' + [r,g,b].map(function(v){ return v.toString(16).padStart(2,'0'); }).join('');
}
</script>
