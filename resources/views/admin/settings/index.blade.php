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
                              rows="3" maxlength="500">{{ old('site_description', $settings['site_description']) }}</textarea>
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
                            <option value="">— Aucun —</option>
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

                {{-- Copyright --}}
                <div class="mb-3">
                    <label class="form-label" for="copyright">Copyright</label>
                    <input type="text" id="copyright" name="copyright"
                           class="form-control @error('copyright') is-invalid @enderror"
                           value="{{ old('copyright', $settings['copyright']) }}"
                           maxlength="255">
                    @error('copyright')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Clé du site + Webhook Discord --}}
                <div class="row gx-3">
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="site_key">Clé du site</label>
                        <input type="text" id="site_key" name="site_key"
                               class="form-control @error('site_key') is-invalid @enderror"
                               value="{{ old('site_key', $settings['site_key']) }}"
                               maxlength="255">
                        <div class="form-text">Sera utilisée pour connecter un serveur de jeu ou accéder au marketplace.</div>
                        @error('site_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="posts_webhook">Webhook Discord (articles)</label>
                        <input type="url" id="posts_webhook" name="posts_webhook"
                               class="form-control @error('posts_webhook') is-invalid @enderror"
                               value="{{ old('posts_webhook', $settings['posts_webhook']) }}"
                               maxlength="500"
                               placeholder="https://discord.com/api/webhooks/...">
                        @error('posts_webhook')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
