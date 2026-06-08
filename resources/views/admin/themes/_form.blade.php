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
function hexToHsl(hex) {
    var r = parseInt(hex.slice(1,3),16)/255;
    var g = parseInt(hex.slice(3,5),16)/255;
    var b = parseInt(hex.slice(5,7),16)/255;
    var max = Math.max(r,g,b), min = Math.min(r,g,b);
    var h, s, l = (max+min)/2;
    if (max === min) { h = s = 0; }
    else {
        var d = max - min;
        s = l > 0.5 ? d/(2-max-min) : d/(max+min);
        switch(max) {
            case r: h = ((g-b)/d + (g<b?6:0))/6; break;
            case g: h = ((b-r)/d + 2)/6; break;
            case b: h = ((r-g)/d + 4)/6; break;
        }
    }
    return [h*360, s*100, l*100];
}

function hslToHex(h, s, l) {
    h /= 360; s /= 100; l /= 100;
    var r, g, b;
    if (s === 0) { r = g = b = l; }
    else {
        var q = l < 0.5 ? l*(1+s) : l+s-l*s;
        var p = 2*l-q;
        var hue2rgb = function(p,q,t){ if(t<0)t+=1; if(t>1)t-=1; if(t<1/6)return p+(q-p)*6*t; if(t<1/2)return q; if(t<2/3)return p+(q-p)*(2/3-t)*6; return p; };
        r = hue2rgb(p,q,h+1/3); g = hue2rgb(p,q,h); b = hue2rgb(p,q,h-1/3);
    }
    return '#'+[r,g,b].map(function(v){ return Math.round(v*255).toString(16).padStart(2,'0'); }).join('');
}

function darkToLight(hex) {
    var hsl = hexToHsl(hex);
    var h = hsl[0], s = hsl[1], l = hsl[2];
    // Invert lightness: dark (l≈5) → light (l≈92), light (l≈90) → dark (l≈15)
    var newL = 100 - l;
    // Clamp: backgrounds stay light (85-96), text stays dark (10-25)
    newL = Math.min(96, Math.max(8, newL));
    // Reduce saturation slightly for a neutral look
    var newS = Math.min(s, 12);
    return hslToHex(h, newS, newL);
}

function generateLightColors() {
    document.querySelectorAll('.dark-color').forEach(function(input) {
        var lightInput = document.querySelector('[name="' + input.dataset.lightTarget + '"]');
        if (lightInput) lightInput.value = darkToLight(input.value);
    });
}

// Auto-generate on each dark color change
document.querySelectorAll('.dark-color').forEach(function(input) {
    input.addEventListener('input', generateLightColors);
});

// Button: generate + switch to light tab
document.getElementById('generateLight').addEventListener('click', function() {
    generateLightColors();
    var lightTabBtn = document.querySelector('[data-bs-target="#lightTab"]');
    if (lightTabBtn) bootstrap.Tab.getOrCreateInstance(lightTabBtn).show();
});

// Generate on load if it's a new theme (no existing light values)
@if(!isset($theme))
generateLightColors();
@endif
</script>
