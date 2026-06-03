@csrf
<div class="mb-3">
    <label class="form-label" for="themeName">Nom</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="themeName" name="name" value="{{ old('name', $theme->name ?? '') }}" required>
    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="bodyBg">Fond principal</label>
        <input type="color" class="form-control form-control-color" id="bodyBg" name="body_bg"
               value="{{ old('body_bg', $theme->body_bg ?? '#111113') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="contentBg">Fond contenu</label>
        <input type="color" class="form-control form-control-color" id="contentBg" name="content_bg"
               value="{{ old('content_bg', $theme->content_bg ?? '#2E2E34') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="cardBg">Fond carte</label>
        <input type="color" class="form-control form-control-color" id="cardBg" name="card_bg"
               value="{{ old('card_bg', $theme->card_bg ?? '#212227') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="accentColor">Couleur accent</label>
        <input type="color" class="form-control form-control-color" id="accentColor" name="accent_color"
               value="{{ old('accent_color', $theme->accent_color ?? '#EFB702') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="headerBg">Fond header</label>
        <input type="color" class="form-control form-control-color" id="headerBg" name="header_bg"
               value="{{ old('header_bg', $theme->header_bg ?? '#1a1b1f') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="textColor">Couleur texte</label>
        <input type="color" class="form-control form-control-color" id="textColor" name="text_color"
               value="{{ old('text_color', $theme->text_color ?? '#e2e8f0') }}">
    </div>
</div>
<div class="mt-3"></div>
