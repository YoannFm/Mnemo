@csrf
<div class="mb-3">
    <label class="form-label" for="titleInput">Titre</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="titleInput" name="title" value="{{ old('title', $post->title ?? '') }}" required>
    @error('title')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="slugInput">Slug</label>
    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slugInput" name="slug" value="{{ old('slug', $post->slug ?? '') }}" required>
    @error('slug')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="contentArea">Contenu</label>
    <textarea class="form-control @error('content') is-invalid @enderror" id="contentArea" name="content" rows="10">{{ old('content', $post->content ?? '') }}</textarea>
    @error('content')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="publishedInput">Publié le</label>
    <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" id="publishedInput" name="published_at" value="{{ old('published_at', isset($post->published_at) ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
    @error('published_at')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
</div>
