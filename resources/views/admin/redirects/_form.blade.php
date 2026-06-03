@csrf
<div class="mb-3">
    <label class="form-label" for="sourceInput">URL source</label>
    <input type="text" class="form-control @error('source') is-invalid @enderror" id="sourceInput" name="source" value="{{ old('source', $redirect->source ?? '') }}" placeholder="/ancienne-page" required>
    @error('source')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
    <small class="form-text text-muted">Commencez par / (ex: /ancienne-page)</small>
</div>
<div class="row">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="targetInput">URL cible</label>
        <input type="text" class="form-control @error('target') is-invalid @enderror" id="targetInput" name="target" value="{{ old('target', $redirect->target ?? '') }}" required>
        @error('target')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
    </div>
    <div class="mb-3 col-md-3">
        <label class="form-label" for="typeSelect">Code HTTP</label>
        <select class="form-select @error('type') is-invalid @enderror" id="typeSelect" name="type" required>
            <option value="301" @selected(old('type', $redirect->type ?? 301) == 301)>301 - Permanent</option>
            <option value="302" @selected(old('type', $redirect->type ?? 301) == 302)>302 - Temporaire</option>
        </select>
        @error('type')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
    </div>
    <div class="mb-3 col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" value="1" @checked(old('is_enabled', $redirect->is_enabled ?? true))>
            <label class="form-check-label" for="enableSwitch">Activée</label>
        </div>
    </div>
</div>
