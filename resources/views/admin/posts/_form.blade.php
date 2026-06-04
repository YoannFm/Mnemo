@csrf

<div class="mb-3">
    <label class="form-label" for="titleInput">Titre *</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror"
           id="titleInput" name="title" value="{{ old('title', $post->title ?? '') }}" required>
    @error('title')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="descriptionInput">Description</label>
    <input type="text" class="form-control @error('description') is-invalid @enderror"
           id="descriptionInput" name="description" value="{{ old('description', $post->description ?? '') }}">
    @error('description')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="imageInput">Image</label>
    <input type="file" class="form-control @error('image') is-invalid @enderror"
           id="imageInput" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
           onchange="previewImage(this)">
    @error('image')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    <img src="{{ (isset($post) && $post->image) ? $post->imageUrl() : '#' }}"
         class="mt-2 img-fluid rounded {{ (isset($post) && $post->image) ? '' : 'd-none' }}"
         style="max-height:200px" alt="Apercu" id="imagePreview">
</div>

<div class="mb-3">
    <label class="form-label" for="slugInput">Slug *</label>
    <div class="input-group @error('slug') has-validation @enderror">
        <span class="input-group-text">{{ url('/news') }}/</span>
        <input type="text" class="form-control @error('slug') is-invalid @enderror"
               id="slugInput" name="slug" value="{{ old('slug', $post->slug ?? '') }}" required>
        @error('slug')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="contentArea">Contenu *</label>
    <textarea class="form-control html-editor @error('content') is-invalid @enderror"
              id="contentArea" name="content" rows="5">{{ old('content', $post->content ?? '') }}</textarea>
    @error('content')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="publishedInput">Publié le</label>
    <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
           id="publishedInput" name="published_at"
           value="{{ old('published_at', isset($post->published_at) ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
    @error('published_at')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    <small class="form-text text-muted">Si la date est dans le futur, l'article ne sera pas visible.</small>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="pinnedSwitch" name="is_pinned" value="1"
           @checked(old('is_pinned', $post->is_pinned ?? false))>
    <label class="form-check-label" for="pinnedSwitch">Epingler</label>
</div>

<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
(function () {
    function initTinyMCE() {
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        if (tinymce.get('contentArea')) tinymce.remove('#contentArea');
        tinymce.init({
            selector: '#contentArea',
            base_url: '{{ asset('vendor/tinymce') }}',
            license_key: 'gpl',
            promotion: false,
            height: 400,
            plugins: 'searchreplace autolink code link lists table',
            toolbar: 'blocks bold italic underline strikethrough | link | alignleft aligncenter alignright | bullist numlist | removeformat code | undo redo',
            skin: dark ? 'oxide-dark' : 'oxide',
            content_css: dark ? 'dark' : 'default',
        });
    }

    initTinyMCE();

    new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            if (m.attributeName === 'data-bs-theme') initTinyMCE();
        });
    }).observe(document.documentElement, { attributes: true });
})();

function previewImage(input) {
    if (input.files && input.files[0]) {
        var preview = document.getElementById('imagePreview');
        preview.src = URL.createObjectURL(input.files[0]);
        preview.classList.remove('d-none');
    }
}
</script>
