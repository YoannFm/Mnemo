@csrf

<div class="mb-3">
    <label class="form-label" for="titleInput">Titre *</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror"
           id="titleInput" name="title" value="{{ old('title', $page->title ?? '') }}" required>
    @error('title')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="descriptionInput">Description</label>
    <input type="text" class="form-control @error('description') is-invalid @enderror"
           id="descriptionInput" name="description" value="{{ old('description', $page->description ?? '') }}">
    @error('description')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="slugInput">Slug</label>
    <div class="input-group @error('slug') has-validation @enderror">
        <span class="input-group-text">{{ url('/') }}/p/</span>
        <input type="text" class="form-control @error('slug') is-invalid @enderror"
               id="slugInput" name="slug" value="{{ old('slug', $page->slug ?? '') }}" required>
        @error('slug')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="contentArea">Contenu *</label>
    <textarea class="form-control html-editor @error('content') is-invalid @enderror"
              id="contentArea" name="content" rows="5">{{ old('content', $page->content ?? '') }}</textarea>
    @error('content')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="checkbox" class="form-check-input" id="restrictedSwitch" name="restricted"
               data-bs-toggle="collapse" data-bs-target="#rolesGroup"
               @checked(isset($page) && $page->isRestricted())>
        <label class="form-check-label" for="restrictedSwitch">Restreindre par role</label>
    </div>
</div>

<div id="rolesGroup" class="{{ (isset($page) && $page->isRestricted()) ? 'show' : 'collapse' }}">
    <div class="card card-body mb-3">
        <div class="row">
            @foreach($roles as $role)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="mb-2 form-check">
                        <input type="checkbox" class="form-check-input"
                               id="role{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                               @checked(in_array($role->id, old('roles', isset($page) ? $page->roles->modelKeys() : []), true))>
                        <label class="form-check-label" for="role{{ $role->id }}">
                            <span class="badge" style="{{ $role->getBadgeStyle() }}">
                                @if($role->icon) <i class="{{ $role->icon }}"></i> @endif
                                {{ $role->name }}
                            </span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" value="1"
           @checked(old('is_enabled', $page->is_enabled ?? true))>
    <label class="form-check-label" for="enableSwitch">Active</label>
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
</script>
