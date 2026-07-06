<x-admin-layout>
    <x-slot name="pageTitle">Paramètres généraux</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Paramètres du site</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf

                {{-- Nom + URL --}}
                <div class="row gx-3">
                    <div class="mb-3 col-md-5">
                        <label class="form-label" for="site_name">Nom du site</label>
                        <input type="text" id="site_name" name="site_name"
                               class="form-control @error('site_name') is-invalid @enderror"
                               value="{{ old('site_name', $settings['site_name']) }}"
                               maxlength="100" required>
                        @error('site_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3 col-md-7">
                        <label class="form-label" for="site_url">URL du site</label>
                        <input type="url" id="site_url" name="site_url"
                               class="form-control @error('site_url') is-invalid @enderror"
                               value="{{ old('site_url', $settings['site_url']) }}"
                               maxlength="255">
                        @error('site_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label" for="site_description">Description du site</label>
                    <textarea id="site_description" name="site_description"
                              class="form-control @error('site_description') is-invalid @enderror"
                              rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                    @error('site_description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Mots-clés --}}
                <div class="mb-3">
                    <label class="form-label" for="site_keywords">Mots-clés</label>
                    <input type="text" id="site_keywords" name="site_keywords"
                           class="form-control @error('site_keywords') is-invalid @enderror"
                           value="{{ old('site_keywords', $settings['site_keywords']) }}"
                           maxlength="500" placeholder="mémorisation, flashcard, anki">
                    <div class="form-text">Mots-clés séparés par des virgules, utilisés pour le référencement.</div>
                    @error('site_keywords')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Logo --}}
                <div class="mb-3">
                    <label class="form-label" for="site_logo">Logo</label>
                    <div class="input-group">
                        <a class="btn btn-outline-secondary" href="{{ route('admin.images.create') }}" target="_blank" title="Uploader une image">
                            <i class="bi bi-upload"></i>
                        </a>
                        <select id="site_logo" name="site_logo"
                                class="form-select @error('site_logo') is-invalid @enderror">
                            <option value="">- Aucun -</option>
                            @foreach($images as $image)
                                <option value="{{ $image->file }}"
                                    {{ old('site_logo', $settings['site_logo']) === $image->file ? 'selected' : '' }}>
                                    {{ $image->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('site_logo')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                </div>

                {{-- Champs obligatoires pour les items --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Champs obligatoires pour les items</label>
                    <div class="d-flex flex-wrap gap-3">
                        @php
                            $cbFields = [
                                'item_required_name_fr'  => ['label' => 'Nom principal', 'default' => '1'],
                                'item_required_name_alt' => ['label' => 'Nom alternatif', 'default' => '1'],
                                'item_required_photo'    => ['label' => 'Photo', 'default' => '0'],
                                'item_required_function' => ['label' => 'Description / Fonction', 'default' => '0'],
                            ];
                        @endphp
                        @foreach ($cbFields as $key => $cfg)
                            @php $checked = old($key, $settings[$key] ?? $cfg['default']) === '1'; @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1"
                                       {{ $checked ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $key }}">{{ $cfg['label'] }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Cochez les champs qui doivent être remplis lors de la création ou modification d'un item.</div>
                </div>

                {{-- Fuseau horaire + Langue --}}
                <div class="row gx-3">
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="timezone">Fuseau horaire</label>
                        <select id="timezone" name="timezone"
                                class="form-select @error('timezone') is-invalid @enderror">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}"
                                    {{ old('timezone', $settings['timezone']) === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                        @error('timezone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="locale">Langue</label>
                        <select id="locale" name="locale"
                                class="form-select @error('locale') is-invalid @enderror">
                            <option value="fr" {{ old('locale', $settings['locale']) === 'fr' ? 'selected' : '' }}>Français</option>
                            <option value="en" {{ old('locale', $settings['locale']) === 'en' ? 'selected' : '' }}>English</option>
                        </select>
                        @error('locale')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Webhook Discord --}}
                <div class="mb-3">
                    <label class="form-label" for="posts_webhook">Webhook Discord (articles)</label>
                    <input type="url" id="posts_webhook" name="posts_webhook"
                           class="form-control @error('posts_webhook') is-invalid @enderror"
                           value="{{ old('posts_webhook', $settings['posts_webhook']) }}"
                           maxlength="500"
                           placeholder="https://discord.com/api/webhooks/...">
                    @error('posts_webhook')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Examens --}}
                <div class="mb-3">
                    <label class="form-label" for="exam_default_expires_days">Expiration par défaut des examens (jours)</label>
                    <input type="number" id="exam_default_expires_days" name="exam_default_expires_days"
                           class="form-control @error('exam_default_expires_days') is-invalid @enderror"
                           value="{{ old('exam_default_expires_days', $settings['exam_default_expires_days']) }}"
                           min="1" max="365" placeholder="Laisser vide = pas de limite par défaut">
                    <div class="form-text">Pré-remplit le champ "Date d'expiration" lors de la création d'un examen partagé.</div>
                    @error('exam_default_expires_days')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>

<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
(function () {
    function initTinyMCE() {
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        ['site_description'].forEach(function (id) {
            if (tinymce.get(id)) tinymce.remove('#' + id);
            tinymce.init({
                selector: '#' + id,
                base_url: '{{ asset('vendor/tinymce') }}',
                license_key: 'gpl',
                promotion: false,
                height: 200,
                plugins: 'searchreplace autolink code link lists',
                toolbar: 'blocks bold italic underline strikethrough | link | alignleft aligncenter alignright | bullist numlist | removeformat code | undo redo',
                skin: dark ? 'oxide-dark' : 'oxide',
                content_css: dark ? 'dark' : 'default',
            });
        });
    }
    initTinyMCE();
    new MutationObserver(function (m) { m.forEach(function (mm) { if (mm.attributeName === 'data-bs-theme') initTinyMCE(); }); }).observe(document.documentElement, { attributes: true });
})();
</script>
</x-admin-layout>
