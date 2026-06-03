@csrf
<div class="mb-3">
    <label class="form-label" for="titleInput">Titre</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="titleInput" name="title" value="{{ old('title', $page->title ?? '') }}" required>
    @error('title')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="slugInput">Slug</label>
    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slugInput" name="slug" value="{{ old('slug', $page->slug ?? '') }}" required>
    @error('slug')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="contentArea">Contenu (HTML)</label>
    <textarea class="form-control @error('content') is-invalid @enderror" id="contentArea" name="content" rows="10">{{ old('content', $page->content ?? '') }}</textarea>
    @error('content')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="publishedSwitch" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? false))>
    <label class="form-check-label" for="publishedSwitch">Publié</label>
</div>
