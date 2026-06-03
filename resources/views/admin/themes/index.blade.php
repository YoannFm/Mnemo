<x-admin-layout>
    <x-slot name="pageTitle">Thèmes — Couleurs</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-palette me-2"></i>Couleurs du thème</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.themes.update') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label" for="theme_body_bg">Fond de page (--body-bg)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="theme_body_bg" name="theme_body_bg"
                                   class="form-control form-control-color @error('theme_body_bg') is-invalid @enderror"
                                   value="{{ old('theme_body_bg', $colors['theme_body_bg']) }}"
                                   style="width:50px; height:38px; padding:2px;">
                            <span style="display:inline-block;width:32px;height:32px;background:{{ $colors['theme_body_bg'] }};border:1px solid #ccc;border-radius:4px;"></span>
                            <small class="text-muted">{{ $colors['theme_body_bg'] }}</small>
                        </div>
                        @error('theme_body_bg')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="theme_content_bg">Fond du contenu (--content-bg)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="theme_content_bg" name="theme_content_bg"
                                   class="form-control form-control-color @error('theme_content_bg') is-invalid @enderror"
                                   value="{{ old('theme_content_bg', $colors['theme_content_bg']) }}"
                                   style="width:50px; height:38px; padding:2px;">
                            <span style="display:inline-block;width:32px;height:32px;background:{{ $colors['theme_content_bg'] }};border:1px solid #ccc;border-radius:4px;"></span>
                            <small class="text-muted">{{ $colors['theme_content_bg'] }}</small>
                        </div>
                        @error('theme_content_bg')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="theme_card_bg">Fond des cartes (--card-bg)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="theme_card_bg" name="theme_card_bg"
                                   class="form-control form-control-color @error('theme_card_bg') is-invalid @enderror"
                                   value="{{ old('theme_card_bg', $colors['theme_card_bg']) }}"
                                   style="width:50px; height:38px; padding:2px;">
                            <span style="display:inline-block;width:32px;height:32px;background:{{ $colors['theme_card_bg'] }};border:1px solid #ccc;border-radius:4px;"></span>
                            <small class="text-muted">{{ $colors['theme_card_bg'] }}</small>
                        </div>
                        @error('theme_card_bg')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="theme_accent">Couleur accentuée (--accent)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="theme_accent" name="theme_accent"
                                   class="form-control form-control-color @error('theme_accent') is-invalid @enderror"
                                   value="{{ old('theme_accent', $colors['theme_accent']) }}"
                                   style="width:50px; height:38px; padding:2px;">
                            <span style="display:inline-block;width:32px;height:32px;background:{{ $colors['theme_accent'] }};border:1px solid #ccc;border-radius:4px;"></span>
                            <small class="text-muted">{{ $colors['theme_accent'] }}</small>
                        </div>
                        @error('theme_accent')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="theme_header_bg">Fond du header (--header-bg)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="theme_header_bg" name="theme_header_bg"
                                   class="form-control form-control-color @error('theme_header_bg') is-invalid @enderror"
                                   value="{{ old('theme_header_bg', $colors['theme_header_bg']) }}"
                                   style="width:50px; height:38px; padding:2px;">
                            <span style="display:inline-block;width:32px;height:32px;background:{{ $colors['theme_header_bg'] }};border:1px solid #ccc;border-radius:4px;"></span>
                            <small class="text-muted">{{ $colors['theme_header_bg'] }}</small>
                        </div>
                        @error('theme_header_bg')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="theme_text_color">Couleur du texte (--text-color)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="theme_text_color" name="theme_text_color"
                                   class="form-control form-control-color @error('theme_text_color') is-invalid @enderror"
                                   value="{{ old('theme_text_color', $colors['theme_text_color']) }}"
                                   style="width:50px; height:38px; padding:2px;">
                            <span style="display:inline-block;width:32px;height:32px;background:{{ $colors['theme_text_color'] }};border:1px solid #ccc;border-radius:4px;"></span>
                            <small class="text-muted">{{ $colors['theme_text_color'] }}</small>
                        </div>
                        @error('theme_text_color')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                </div>

                <hr class="my-4">

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
